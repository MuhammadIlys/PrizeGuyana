<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use Inertia\Inertia;


class TransactionController extends Controller
{
    /**
     * Display payment transactions belonging to the authenticated customer.
     */
    public function transactions(Request $request)
    {
        $userId = $request->user()->id;

        $query = DB::table('payments')
            ->join(
                'orders',
                'payments.order_id',
                '=',
                'orders.id'
            )
            ->where('orders.user_id', $userId)
            ->select([
                'payments.id',
                'payments.order_id',
                'payments.provider',
                'payments.amount',
                'payments.currency',
                'payments.status',
                'payments.paid_at',
                'payments.created_at',
                'payments.stripe_checkout_session_id',
                'payments.stripe_payment_intent_id',
                'orders.order_number',
            ]);

        $search = trim((string) $request->input('search', ''));

        if ($search !== '') {
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery
                    ->where('orders.order_number', 'like', "%{$search}%")
                    ->orWhere(
                        'payments.stripe_checkout_session_id',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'payments.stripe_payment_intent_id',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        $status = (string) $request->input('status', '');

        if (in_array($status, [
            'pending',
            'paid',
            'failed',
            'cancelled',
        ], true)) {
            $query->where('payments.status', $status);
        }

        $transactions = $query
            ->orderByDesc('payments.created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($payment) => [
                'id' => $payment->id,
                'order_number' => $payment->order_number,
                'provider' => $payment->provider,
                'amount' => number_format(
                    (float) $payment->amount,
                    2,
                    '.',
                    ''
                ),
                'currency' => $payment->currency,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
                'checkout_session_id' =>
                $payment->stripe_checkout_session_id,
                'payment_intent_id' =>
                $payment->stripe_payment_intent_id,
            ]);

        return Inertia::render('User/Orders/Transactions', [
            'transactions' => $transactions,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $status,
            ],
        ]);
    }
    public function transactionDetails(Request $request, int $payment)
    {
        $userId = (int) $request->user()->id;

        // Find the payment only if it belongs to the authenticated user.
        $paymentRecord = DB::table('payments')
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->where('payments.id', $payment)
            ->where('orders.user_id', $userId)
            ->select([
                'payments.id as payment_id',
                'payments.order_id',
                'payments.provider',
                'payments.amount as payment_amount',
                'payments.currency as payment_currency',
                'payments.status as payment_status',
                'payments.paid_at as payment_paid_at',
                'payments.created_at as payment_created_at',

                'orders.order_number',
                'orders.status as order_status',
                'orders.payment_status as order_payment_status',
                'orders.subtotal',
                'orders.total as order_total',
                'orders.currency as order_currency',
                'orders.paid_at as order_paid_at',
                'orders.created_at as order_created_at',
            ])
            ->first();

        // Return 404 if the payment doesn't exist or belongs to another user.
        abort_unless($paymentRecord, 404);

        // Get the items associated with this order.
        $items = DB::table('order_items')
            ->where('order_id', $paymentRecord->order_id)
            ->get();

        // Format transaction information for the frontend.
        $transaction = [
            'id' => $paymentRecord->payment_id,
            'reference' => 'Payment #' . $paymentRecord->payment_id,
            'order_number' => $paymentRecord->order_number,
            'provider' => $paymentRecord->provider,
            'amount' => number_format(
                (float) $paymentRecord->payment_amount,
                2,
                '.',
                ''
            ),
            'currency' => $paymentRecord->payment_currency,
            'status' => $paymentRecord->payment_status,
            'paid_at' => $paymentRecord->payment_paid_at,
            'created_at' => $paymentRecord->payment_created_at,
        ];

        // Format associated order information.
        $order = [
            'order_number' => $paymentRecord->order_number,
            'status' => $paymentRecord->order_status,
            'payment_status' => $paymentRecord->order_payment_status,
            'subtotal' => number_format(
                (float) $paymentRecord->subtotal,
                2,
                '.',
                ''
            ),
            'total' => number_format(
                (float) $paymentRecord->order_total,
                2,
                '.',
                ''
            ),
            'currency' => $paymentRecord->order_currency,
            'paid_at' => $paymentRecord->order_paid_at,
            'created_at' => $paymentRecord->order_created_at,
        ];

        // Retrieve tickets assigned to this order and customer.
        $tickets = DB::table('competition_tickets')
            ->where('order_id', $paymentRecord->order_id)
            ->where('user_id', $userId)
            ->where('status', 'assigned')
            ->orderBy('competition_id')
            ->orderBy('ticket_number')
            ->get([
                'id',
                'competition_id',
                'ticket_number',
                'status',
                'assigned_at',
            ])
            ->map(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'competition_id' => $ticket->competition_id,
                    'ticket_number' => $ticket->ticket_number,
                    'status' => $ticket->status,
                    'assigned_at' => $ticket->assigned_at,
                ];
            })
            ->values();

        return Inertia::render('User/Orders/TransactionDetails', [
            'transaction' => $transaction,
            'order' => $order,
            'items' => $items,
            'tickets' => $tickets,
        ]);
    }
}
