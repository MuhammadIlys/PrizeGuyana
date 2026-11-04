<?php

namespace App\Http\Controllers\User;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController
{
    /**
     * Display the authenticated customer's orders.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        $query = $this->ordersQuery($userId);

        $this->applyOrderFilters($query, $request);

        $orders = $query
            ->orderByDesc('orders.created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($order) => $this->formatOrder($order));

        return Inertia::render('User/Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    /**
     * Display the authenticated customer's paid order history.
     */
    public function history(Request $request): Response
    {
        $userId = $request->user()->id;

        $query = $this->ordersQuery($userId)
            ->where('orders.payment_status', 'paid');

        $this->applyOrderFilters($query, $request);

        $orders = $query
            ->orderByDesc('orders.created_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn($order) => $this->formatOrder($order));

        return Inertia::render('User/Orders/History', [
            'orders' => $orders,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    /**
     * Build an order query scoped to one customer.
     */
    private function ordersQuery(int $userId): Builder
    {
        return DB::table('orders')
            ->where('orders.user_id', $userId)
            ->leftJoin(
                'order_items',
                'orders.id',
                '=',
                'order_items.order_id'
            )
            ->select([
                'orders.id',
                'orders.order_number',
                'orders.status',
                'orders.payment_status',
                'orders.subtotal',
                'orders.total',
                'orders.currency',
                'orders.paid_at',
                'orders.created_at',
                'orders.updated_at',
                DB::raw(
                    'COALESCE(SUM(order_items.quantity), 0) as items_count'
                ),
            ])
            ->groupBy(
                'orders.id',
                'orders.order_number',
                'orders.status',
                'orders.payment_status',
                'orders.subtotal',
                'orders.total',
                'orders.currency',
                'orders.paid_at',
                'orders.created_at',
                'orders.updated_at'
            );
    }

    /**
     * Apply optional search and payment-status filters.
     */
    private function applyOrderFilters(
        Builder $query,
        Request $request
    ): void {
        $search = trim((string) $request->input('search', ''));

        if ($search !== '') {
            $query->where(
                'orders.order_number',
                'like',
                "%{$search}%"
            );
        }

        $status = (string) $request->input('status', '');

        if (in_array($status, [
            'pending',
            'paid',
            'failed',
            'cancelled',
        ], true)) {
            $query->where('orders.payment_status', $status);
        }
    }

    /**
     * Format an order for the Inertia pages.
     */
    private function formatOrder(object $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'subtotal' => number_format(
                (float) $order->subtotal,
                2,
                '.',
                ''
            ),
            'total' => number_format(
                (float) $order->total,
                2,
                '.',
                ''
            ),
            'currency' => $order->currency,
            'items_count' => (int) $order->items_count,
            'paid_at' => $order->paid_at,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];
    }

    public function orderDetails(Request $request, int $order): Response
    {
        $userId = (int) $request->user()->id;

        // Find the order belonging to the authenticated customer.
        $orderRecord = DB::table('orders')
            ->where('id', $order)
            ->where('user_id', $userId)
            ->first();

        // Return 404 if the order does not belong to this customer.
        abort_unless($orderRecord, 404);

        // Retrieve items associated with this order.
        $items = DB::table('order_items')
            ->where('order_id', $orderRecord->id)
            ->get();

        // Retrieve tickets assigned to this order and customer.
        $tickets = DB::table('competition_tickets')
            ->where('order_id', $orderRecord->id)
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
            ->map(function ($ticket) use ($items) {
                $item = $items->first(
                    fn($item) =>
                    (int) ($item->competition_id ?? 0) ===
                        (int) $ticket->competition_id
                );

                return [
                    'id' => $ticket->id,
                    'competition_id' => $ticket->competition_id,
                    'competition_title' => $item->competition_title
                        ?? $item->competition_name
                        ?? 'Competition',
                    'ticket_number' => $ticket->ticket_number,
                    'status' => $ticket->status,
                    'assigned_at' => $ticket->assigned_at,
                ];
            })
            ->values();

        // Retrieve payment attempts associated with this order.
        $payments = DB::table('payments')
            ->where('order_id', $orderRecord->id)
            ->select([
                'id',
                'order_id',
                'provider',
                'amount',
                'currency',
                'status',
                'paid_at',
                'created_at',
            ])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'reference' => 'Payment #' . $payment->id,
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
                ];
            })
            ->values();

        // Format order information for the Vue page.
        $orderData = [
            'id' => $orderRecord->id,
            'order_number' => $orderRecord->order_number,
            'status' => $orderRecord->status,
            'payment_status' => $orderRecord->payment_status,
            'subtotal' => number_format(
                (float) $orderRecord->subtotal,
                2,
                '.',
                ''
            ),
            'total' => number_format(
                (float) $orderRecord->total,
                2,
                '.',
                ''
            ),
            'currency' => $orderRecord->currency,
            'paid_at' => $orderRecord->paid_at,
            'created_at' => $orderRecord->created_at,
            'updated_at' => $orderRecord->updated_at,
            'items_count' => (int) $items->sum(
                fn($item) => (int) ($item->quantity ?? 1)
            ),
        ];

        // Render the Order Details page.
        return Inertia::render('User/Orders/OrderDetails', [
            'order' => $orderData,
            'items' => $items,
            'tickets' => $tickets,
            'transactions' => $payments,
        ]);
    }
}
