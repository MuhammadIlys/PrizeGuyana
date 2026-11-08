
<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use App\Models\User;
use App\Services\NotificationService;
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

        // Notification flags and collected Instant Win winners.
        $shouldNotify = false;
        $instantWinWinners = [];

        try {
            DB::transaction(function () use (
                $session,
                $orderId,
                $paymentId,
                &$shouldNotify,
                &$instantWinWinners
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
                        ->orderBy('id')
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

                    /*
                     * Find pending Instant Win records belonging
                     * to the tickets purchased in this order.
                     */
                    $winningRecords = DB::table('prize_winning_tickets as pwt')
                        ->join(
                            'competition_prizes as cp',
                            'cp.id',
                            '=',
                            'pwt.prize_id'
                        )
                        ->join(
                            'competition_tickets as ct',
                            'ct.id',
                            '=',
                            'pwt.ticket_id'
                        )
                        ->where('pwt.competition_id', $item->competition_id)
                        ->whereIn('pwt.ticket_id', $ticketIds)
                        ->where('pwt.winning_method', 'instant_win')
                        ->where('pwt.status', 'pending')
                        ->select([
                            'pwt.id',
                            'cp.name as prize_name',
                            'ct.ticket_number',
                        ])
                        ->lockForUpdate()
                        ->get();

                    if ($winningRecords->isNotEmpty()) {
                        $winningIds = $winningRecords->pluck('id')->all();

                        /*
                         * Mark matching Instant Win records as won.
                         */
                        $winningUpdated = DB::table('prize_winning_tickets')
                            ->whereIn('id', $winningIds)
                            ->where('status', 'pending')
                            ->update([
                                'winner_user_id' => $order->user_id,
                                'order_id' => $orderId,
                                'status' => 'won',
                                'won_at' => now(),
                                'updated_at' => now(),
                            ]);

                        if ($winningUpdated !== count($winningIds)) {
                            throw new \RuntimeException(
                                'Instant Win records could not be updated safely.'
                            );
                        }

                        /*
                         * Collect winning details for notifications
                         * after the transaction commits.
                         */
                        foreach ($winningRecords as $winner) {
                            $instantWinWinners[] = [
                                'winning_id' => $winner->id,
                                'prize_name' => $winner->prize_name,
                                'ticket_number' => $winner->ticket_number,
                            ];
                        }
                    }
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

                // Notify only after successful processing.
                $shouldNotify = true;
            }, 3);
        } catch (Throwable $exception) {
            report($exception);

            /*
             * Return 500 so Stripe can retry transient failures.
             * Do not mark the order paid if ticket assignment fails.
             */
            return response('Payment processing failed.', 500);
        }

        /*
         * Send notifications only after the transaction commits.
         */
        if ($shouldNotify) {
            try {
                $user = User::find(
                    DB::table('orders')
                        ->where('id', $orderId)
                        ->value('user_id')
                );

                if ($user) {
                    $notificationService = app(NotificationService::class);

                    /*
                     * Customer: Payment successful.
                     * Destination: Exact order details.
                     */
                    $notificationService->send(
                        $user,
                        'Payment Successful',
                        "Your payment for order #{$orderId} has been received successfully.",
                        route('user.orders.show', [
                            'order' => $orderId,
                        ]),
                        'payment'
                    );

                    /*
                     * Customer: Tickets assigned.
                     * Destination: My Tickets.
                     */
                    $notificationService->send(
                        $user,
                        'Tickets Assigned',
                        "Your tickets for order #{$orderId} have been assigned. You can now view them in My Tickets.",
                        route('user.tickets.index'),
                        'ticket_assignment'
                    );

                    /*
                     * Admin: New paid order.
                     */
                    $notificationService->notifyAdmins(
                        'New Paid Order',
                        "Order #{$orderId} has been paid successfully and tickets have been assigned.",
                        null,
                        'payment'
                    );

                    /*
                     * Instant Win notifications.
                     */
                    foreach ($instantWinWinners as $winner) {
                        $prizeName = $winner['prize_name'];
                        $ticketNumber = $winner['ticket_number'];

                        $winningTicketUrl = route('user.prizes.show', [
                            'winningTicket' => Crypt::encryptString(
                                (string) $winner['winning_id']
                            ),
                        ]);

                        /*
                         * Customer: Instant Win prize awarded.
                         * Destination: Exact winning prize details.
                         */
                        $notificationService->send(
                            $user,
                            'Congratulations! You Won!',
                            "Your ticket #{$ticketNumber} has won an Instant Win prize: {$prizeName}. Click to view your prize and submit your claim.",
                            $winningTicketUrl,
                            'instant_win'
                        );

                        /*
                         * Admin: Instant Win prize awarded.
                         */
                        $notificationService->notifyAdmins(
                            'Instant Win Prize Awarded',
                            "Customer {$user->first_name} {$user->last_name} won {$prizeName} with ticket #{$ticketNumber} in order #{$orderId}.",
                            route('admin.orders.show', [
                                'order' => $orderId,
                            ]),
                            'instant_win'
                        );
                    }
                }
            } catch (Throwable $exception) {
                // Notification failure must not affect successful payment processing.
                report($exception);
            }
        }

        return response('Webhook processed successfully.', 200);
    }
}
