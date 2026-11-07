<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;
use Throwable;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $webhookSecret = config('services.stripe.webhook_secret');

        if (empty($webhookSecret)) {
            report(new \RuntimeException(
                'Stripe webhook secret is not configured.'
            ));

            return response('Webhook is not configured.', 500);
        }

        /*
         * Verify that the request genuinely came from Stripe.
         */
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                $webhookSecret
            );
        } catch (
            UnexpectedValueException |
            SignatureVerificationException $exception
        ) {
            return response('Invalid Stripe webhook signature.', 400);
        }

        /*
         * Handle only successful Checkout Session events.
         */
        if (!in_array($event->type, [
            'checkout.session.completed',
            'checkout.session.async_payment_succeeded',
        ], true)) {
            return response('Event ignored.', 200);
        }

        $session = $event->data->object;

        if (($session->payment_status ?? null) !== 'paid') {
            return response('Payment is not completed.', 200);
        }

        $orderId = (int) ($session->metadata->order_id ?? 0);
        $paymentId = (int) ($session->metadata->payment_id ?? 0);

        if (!$orderId || !$paymentId) {
            report(new \RuntimeException(
                'Stripe Checkout Session is missing order or payment metadata.'
            ));

            return response('Missing payment metadata.', 400);
        }

        try {
            DB::transaction(function () use (
                $session,
                $orderId,
                $paymentId
            ) {
                /*
                 * Lock the order and payment to prevent concurrent
                 * webhook requests from processing them simultaneously.
                 */
                $order = DB::table('orders')
                    ->where('id', $orderId)
                    ->lockForUpdate()
                    ->first();

                $payment = DB::table('payments')
                    ->where('id', $paymentId)
                    ->where('order_id', $orderId)
                    ->lockForUpdate()
                    ->first();

                if (!$order || !$payment) {
                    throw new \RuntimeException(
                        'The order or payment record does not exist.'
                    );
                }

                /*
                 * Ensure the Stripe session belongs to this payment.
                 */
                if (
                    $payment->stripe_checkout_session_id !== $session->id
                ) {
                    throw new \RuntimeException(
                        'Stripe Checkout Session ID does not match.'
                    );
                }

                /*
                 * Verify the amount and currency.
                 * Stripe returns currency in lowercase, while our
                 * database may store it in uppercase.
                 */
                $expectedAmount = (int) round(
                    (float) $order->total * 100
                );

                $stripeAmount = $session->amount_total ?? null;

                $stripeCurrency = strtolower(
                    (string) ($session->currency ?? '')
                );

                $orderCurrency = strtolower(
                    (string) $order->currency
                );

                if (
                    $stripeAmount === null ||
                    (int) $stripeAmount !== $expectedAmount ||
                    $stripeCurrency !== $orderCurrency
                ) {
                    throw new \RuntimeException(
                        'Stripe payment amount or currency does not match the order.'
                    );
                }

                /*
                 * Idempotency: do not assign tickets twice.
                 */
                if (
                    $order->payment_status === 'paid' &&
                    $payment->status === 'paid'
                ) {
                    return;
                }

                if (
                    $order->payment_status === 'paid' ||
                    $payment->status === 'paid'
                ) {
                    throw new \RuntimeException(
                        'Order and payment statuses are inconsistent.'
                    );
                }

                /*
                 * Load the purchased competition items.
                 */
                $items = DB::table('order_items')
                    ->where('order_id', $orderId)
                    ->get();

                if ($items->isEmpty()) {
                    throw new \RuntimeException(
                        'The order contains no items.'
                    );
                }

                /*
                 * Assign existing, pre-generated tickets.
                 * If any item cannot be fulfilled, the entire
                 * transaction rolls back.
                 */
                foreach ($items as $item) {
                    $quantity = (int) $item->quantity;

                    if ($quantity < 1) {
                        throw new \RuntimeException(
                            'The order contains an invalid ticket quantity.'
                        );
                    }

                    $tickets = DB::table('competition_tickets')
                        ->where(
                            'competition_id',
                            $item->competition_id
                        )
                        ->where('status', 'available')
                        ->inRandomOrder()
                        ->limit($quantity)
                        ->lockForUpdate()
                        ->get();

                    if ($tickets->count() !== $quantity) {
                        throw new \RuntimeException(
                            'Insufficient available tickets for competition ID '
                                . $item->competition_id
                        );
                    }

                    $ticketIds = $tickets->pluck('id')->all();

                    $updated = DB::table('competition_tickets')
                        ->whereIn('id', $ticketIds)
                        ->where('status', 'available')
                        ->update([
                            'status' => 'assigned',
                            'user_id' => $order->user_id,
                            'order_id' => $orderId,
                            'assigned_at' => now(),
                            'updated_at' => now(),
                        ]);

                    if ($updated !== $quantity) {
                        throw new \RuntimeException(
                            'Ticket assignment could not be completed safely.'
                        );
                    }
                    // Link any Instant Win records to the ticket owner.
                    DB::table('prize_winning_tickets')
                        ->where('competition_id', $item->competition_id)
                        ->whereIn('ticket_id', $ticketIds)
                        ->where('winning_method', 'instant_win')
                        ->where('status', 'pending')
                        ->update([
                            'winner_user_id' => $order->user_id,
                            'order_id' => $orderId,
                            'status' => 'won',
                            'won_at' => now(),
                            'updated_at' => now(),
                        ]);
                }

                /*
                 * Update payment and order only after all tickets
                 * have been assigned successfully.
                 */
                $paymentIntentId = is_string($session->payment_intent ?? null)
                    ? $session->payment_intent
                    : null;

                DB::table('payments')
                    ->where('id', $paymentId)
                    ->update([
                        'status' => 'paid',
                        'stripe_payment_intent_id' => $paymentIntentId,
                        'paid_at' => now(),
                        'updated_at' => now(),
                    ]);

                DB::table('orders')
                    ->where('id', $orderId)
                    ->update([
                        'status' => 'paid',
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                        'updated_at' => now(),
                    ]);
            }, 3);
        } catch (Throwable $exception) {
            report($exception);

            /*
             * Return 500 so Stripe can retry transient failures.
             * Do not mark the order paid if ticket assignment fails.
             */
            return response('Payment processing failed.', 500);
        }

        return response('Webhook processed successfully.', 200);
    }
}
