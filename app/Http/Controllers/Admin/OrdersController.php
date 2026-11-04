<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrdersController extends Controller
{
    /**
     * Display all orders.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:30'],
            'payment_status' => ['nullable', 'string', 'max:30'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Orders Query
        |--------------------------------------------------------------------------
        */

        $ordersQuery = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->leftJoinSub(
                DB::table('order_items')
                    ->select(
                        'order_id',
                        DB::raw('SUM(quantity) as items_count')
                    )
                    ->groupBy('order_id'),
                'item_summary',
                'item_summary.order_id',
                '=',
                'orders.id'
            )
            ->select(
                'orders.id',
                'orders.user_id',
                'orders.order_number',
                'orders.status',
                'orders.payment_status',
                'orders.subtotal',
                'orders.total',
                'orders.currency',
                'orders.paid_at',
                'orders.created_at',
                'users.name as customer_name',
                'users.email as customer_email',
                DB::raw('COALESCE(item_summary.items_count, 0) as items_count')
            );

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $ordersQuery->where(function ($query) use ($search) {
                $query->where('orders.order_number', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['status'])) {
            $ordersQuery->where('orders.status', $filters['status']);
        }

        if (!empty($filters['payment_status'])) {
            $ordersQuery->where(
                'orders.payment_status',
                $filters['payment_status']
            );
        }

        $orders = $ordersQuery
            ->orderByDesc('orders.created_at')
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Encrypt Order and Customer IDs
        |--------------------------------------------------------------------------
        */

        $orders->getCollection()->transform(function ($order) {
            return [
                'encrypted_id' => Crypt::encryptString((string) $order->id),
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'subtotal' => $order->subtotal,
                'total' => $order->total,
                'currency' => $order->currency,
                'paid_at' => $order->paid_at,
                'created_at' => $order->created_at,
                'customer_encrypted_id' => Crypt::encryptString(
                    (string) $order->user_id
                ),
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'items_count' => (int) $order->items_count,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Order Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total_orders' => DB::table('orders')->count(),

            'paid_orders' => DB::table('orders')
                ->where('payment_status', 'paid')
                ->count(),

            'pending_orders' => DB::table('orders')
                ->where('payment_status', 'pending')
                ->count(),

            'cancelled_orders' => DB::table('orders')
                ->where('status', 'cancelled')
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'stats' => $stats,

            'filters' => [
                'search' => $filters['search'] ?? '',
                'status' => $filters['status'] ?? '',
                'payment_status' => $filters['payment_status'] ?? '',
            ],
        ]);
    }

    /**
     * Display a specific order.
     */
    public function show($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Decrypt Order ID
        |--------------------------------------------------------------------------
        */

        try {
            $orderId = Crypt::decryptString($id);
        } catch (DecryptException $exception) {
            abort(404);
        }

        if (!ctype_digit($orderId) || (int) $orderId < 1) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Order & Customer Information
        |--------------------------------------------------------------------------
        */

        $order = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->where('orders.id', (int) $orderId)
            ->select(
                'orders.*',
                'users.name as customer_name',
                'users.email as customer_email',
                'users.phone_number as customer_phone',
                'users.country as customer_country'
            )
            ->first();

        abort_if(!$order, 404);

        /*
        |--------------------------------------------------------------------------
        | Order Items
        |--------------------------------------------------------------------------
        */

        $items = DB::table('order_items')
            ->where('order_id', $order->id)
            ->select(
                'id',
                'competition_id',
                'competition_title',
                'quantity',
                'unit_price',
                'line_total',
                'currency',
                'created_at'
            )
            ->orderBy('id')
            ->get()
            ->map(function ($item) {
                return [
                    'competition_encrypted_id' => $item->competition_id
                        ? Crypt::encryptString((string) $item->competition_id)
                        : null,
                    'competition_title' => $item->competition_title,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                    'currency' => $item->currency,
                    'created_at' => $item->created_at,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Payment Records
        |--------------------------------------------------------------------------
        */

        $payments = DB::table('payments')
            ->where('order_id', $order->id)
            ->select(
                'id',
                'provider',
                'stripe_checkout_session_id',
                'stripe_payment_intent_id',
                'amount',
                'currency',
                'status',
                'paid_at',
                'created_at'
            )
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($payment) {
                return [
                    'encrypted_id' => Crypt::encryptString(
                        (string) $payment->id
                    ),
                    'provider' => $payment->provider,
                    'checkout_session_id' => $payment->stripe_checkout_session_id,
                    'payment_intent_id' => $payment->stripe_payment_intent_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'paid_at' => $payment->paid_at,
                    'created_at' => $payment->created_at,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Ticket Information
        |--------------------------------------------------------------------------
        */

        $tickets = DB::table('competition_tickets')
            ->join(
                'my_competitions',
                'competition_tickets.competition_id',
                '=',
                'my_competitions.id'
            )
            ->where('competition_tickets.order_id', $order->id)
            ->select(
                'competition_tickets.id',
                'competition_tickets.ticket_number',
                'competition_tickets.status',
                'competition_tickets.assigned_at',
                'my_competitions.id as competition_id',
                'my_competitions.title as competition_title'
            )
            ->orderBy('competition_tickets.id')
            ->get()
            ->map(function ($ticket) {
                return [
                    'encrypted_id' => Crypt::encryptString(
                        (string) $ticket->id
                    ),
                    'ticket_number' => $ticket->ticket_number,
                    'status' => $ticket->status,
                    'assigned_at' => $ticket->assigned_at,
                    'competition_encrypted_id' => Crypt::encryptString(
                        (string) $ticket->competition_id
                    ),
                    'competition_title' => $ticket->competition_title,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Admin/Orders/Show', [
            'order' => [
                'encrypted_id' => Crypt::encryptString(
                    (string) $order->id
                ),
                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'subtotal' => $order->subtotal,
                'total' => $order->total,
                'currency' => $order->currency,
                'paid_at' => $order->paid_at,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,

                'customer' => [
                    'encrypted_id' => Crypt::encryptString(
                        (string) $order->user_id
                    ),
                    'name' => $order->customer_name,
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                    'country' => $order->customer_country,
                ],

                'items' => $items,
                'payments' => $payments,
                'tickets' => $tickets,
            ],
        ]);
    }
}
