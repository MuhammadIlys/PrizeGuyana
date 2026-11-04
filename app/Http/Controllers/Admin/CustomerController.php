<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerController extends Controller
{
    /**
     * Display registered customers.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Customers Query
        |--------------------------------------------------------------------------
        */

        $query = DB::table('users')
            ->where('users.role', 'user')
            ->select(
                'users.id',
                'users.first_name',
                'users.last_name',
                'users.name',
                'users.email',
                'users.phone_number',
                'users.country',
                'users.email_verified_at',
                'users.created_at'
            );

        // Search by customer name or email.
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where('users.first_name', 'like', "%{$search}%")
                    ->orWhere('users.last_name', 'like', "%{$search}%")
                    ->orWhere('users.name', 'like', "%{$search}%")
                    ->orWhere('users.email', 'like', "%{$search}%");
            });
        }

        // Filter by country.
        if (!empty($filters['country'])) {
            $query->where('users.country', $filters['country']);
        }

        $customers = $query
            ->orderByDesc('users.created_at')
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Page Customer IDs
        |--------------------------------------------------------------------------
        */

        $customerIds = $customers->getCollection()
            ->pluck('id')
            ->all();

        $orderCounts = collect();
        $entryCounts = collect();
        $spending = collect();

        if (!empty($customerIds)) {

            /*
            |--------------------------------------------------------------------------
            | Orders Count
            |--------------------------------------------------------------------------
            */

            $orderCounts = DB::table('orders')
                ->whereIn('user_id', $customerIds)
                ->select(
                    'user_id',
                    DB::raw('COUNT(*) as total_orders')
                )
                ->groupBy('user_id')
                ->pluck('total_orders', 'user_id');

            /*
            |--------------------------------------------------------------------------
            | Purchased Entries Count
            |--------------------------------------------------------------------------
            */

            $entryCounts = DB::table('competition_tickets')
                ->whereIn('user_id', $customerIds)
                ->whereNotNull('order_id')
                ->select(
                    'user_id',
                    DB::raw('COUNT(*) as total_entries')
                )
                ->groupBy('user_id')
                ->pluck('total_entries', 'user_id');

            /*
            |--------------------------------------------------------------------------
            | Spending By Currency
            |--------------------------------------------------------------------------
            */

            $spending = DB::table('orders')
                ->whereIn('user_id', $customerIds)
                ->where('payment_status', 'paid')
                ->select(
                    'user_id',
                    'currency',
                    DB::raw('SUM(total) as total_spent')
                )
                ->groupBy('user_id', 'currency')
                ->orderBy('currency')
                ->get()
                ->groupBy('user_id');
        }

        /*
        |--------------------------------------------------------------------------
        | Prepare Customer Data
        |--------------------------------------------------------------------------
        */

        $customers->getCollection()->transform(function ($customer) use (
            $orderCounts,
            $entryCounts,
            $spending
        ) {
            $name = trim(
                ($customer->first_name ?? '') . ' ' .
                ($customer->last_name ?? '')
            );

            if ($name === '') {
                $name = $customer->name ?? 'Customer';
            }

            $customerSpending = $spending
                ->get($customer->id, collect())
                ->map(fn ($row) => [
                    'currency' => $row->currency,
                    'total' => number_format(
                        (float) $row->total_spent,
                        2,
                        '.',
                        ''
                    ),
                ])
                ->values();

            return [
                'encrypted_id' => Crypt::encryptString((string) $customer->id),

                'name' => $name,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number,
                'country' => $customer->country,

                'email_verified' => !empty($customer->email_verified_at),

                'created_at' => $customer->created_at,

                'orders_count' => (int) (
                    $orderCounts[$customer->id] ?? 0
                ),

                'total_entries' => (int) (
                    $entryCounts[$customer->id] ?? 0
                ),

                'spending_by_currency' => $customerSpending,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Global Statistics
        |--------------------------------------------------------------------------
        */

        $totalCustomers = DB::table('users')
            ->where('role', 'user')
            ->count();

        $newCustomers = DB::table('users')
            ->where('role', 'user')
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        $customersWithOrders = DB::table('users')
            ->where('users.role', 'user')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('orders')
                    ->whereColumn('orders.user_id', 'users.id');
            })
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Country Filters
        |--------------------------------------------------------------------------
        */

        $countries = DB::table('users')
            ->where('role', 'user')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->orderBy('country')
            ->pluck('country');

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,

            'stats' => [
                'total_customers' => $totalCustomers,
                'new_this_month' => $newCustomers,
                'customers_with_orders' => $customersWithOrders,
            ],

            'countries' => $countries,

            'filters' => [
                'search' => $filters['search'] ?? '',
                'country' => $filters['country'] ?? '',
            ],
        ]);
    }

    /**
     * Display an individual customer's profile and activity.
     */
    public function show($id)
    {
        /*
        |--------------------------------------------------------------------------
        | Decrypt Customer ID
        |--------------------------------------------------------------------------
        */

        try {
            $customerId = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            abort(404);
        }

        if (!ctype_digit($customerId) || (int) $customerId <= 0) {
            abort(404);
        }

        /*
        |--------------------------------------------------------------------------
        | Customer Profile
        |--------------------------------------------------------------------------
        */

        $customer = DB::table('users')
            ->where('id', $customerId)
            ->where('role', 'user')
            ->first();

        abort_if(!$customer, 404);

        $name = trim(
            ($customer->first_name ?? '') . ' ' .
            ($customer->last_name ?? '')
        );

        if ($name === '') {
            $name = $customer->name ?? 'Customer';
        }

        /*
        |--------------------------------------------------------------------------
        | Customer Orders
        |--------------------------------------------------------------------------
        */

        $orders = DB::table('orders')
            ->where('user_id', $customer->id)
            ->orderByDesc('created_at')
            ->get();

        $orderIds = $orders->pluck('id')->all();

        $orderItems = collect();

        if (!empty($orderIds)) {
            $orderItems = DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->select(
                    'id',
                    'order_id',
                    'competition_id',
                    'competition_title',
                    'quantity',
                    'unit_price',
                    'line_total',
                    'currency'
                )
                ->get()
                ->groupBy('order_id');
        }

        $orders = $orders->map(function ($order) use ($orderItems) {
            $items = $orderItems
                ->get($order->id, collect())
                ->map(fn ($item) => [
                    'encrypted_id' => Crypt::encryptString((string) $item->id),

                    'competition_encrypted_id' => $item->competition_id
                        ? Crypt::encryptString((string) $item->competition_id)
                        : null,

                    'competition_title' => $item->competition_title,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                    'currency' => $item->currency,
                ])
                ->values();

            return [
                'encrypted_id' => Crypt::encryptString((string) $order->id),

                'order_number' => $order->order_number,
                'status' => $order->status,
                'payment_status' => $order->payment_status,
                'subtotal' => $order->subtotal,
                'total' => $order->total,
                'currency' => $order->currency,

                'items_count' => (int) $items->sum('quantity'),
                'items' => $items,

                'paid_at' => $order->paid_at,
                'created_at' => $order->created_at,
            ];
        });

        /*
        |--------------------------------------------------------------------------
        | Customer Payments
        |--------------------------------------------------------------------------
        */

        $payments = DB::table('payments')
            ->join(
                'orders',
                'payments.order_id',
                '=',
                'orders.id'
            )
            ->where('orders.user_id', $customer->id)
            ->select(
                'payments.id',
                'payments.order_id',
                'orders.order_number',
                'payments.provider',
                'payments.amount',
                'payments.currency',
                'payments.status',
                'payments.paid_at',
                'payments.created_at'
            )
            ->orderByDesc('payments.created_at')
            ->get()
            ->map(fn ($payment) => [
                'encrypted_id' => Crypt::encryptString((string) $payment->id),

                'order_encrypted_id' => Crypt::encryptString(
                    (string) $payment->order_id
                ),

                'order_number' => $payment->order_number,
                'provider' => $payment->provider,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'paid_at' => $payment->paid_at,
                'created_at' => $payment->created_at,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Customer Entries / Tickets
        |--------------------------------------------------------------------------
        */

        $tickets = DB::table('competition_tickets as tickets')
            ->join(
                'my_competitions as competitions',
                'tickets.competition_id',
                '=',
                'competitions.id'
            )
            ->leftJoin(
                'orders',
                'tickets.order_id',
                '=',
                'orders.id'
            )
            ->where('tickets.user_id', $customer->id)
            ->whereNotNull('tickets.order_id')
            ->select(
                'tickets.id',
                'tickets.ticket_number',
                'tickets.status',
                'tickets.assigned_at',
                'tickets.order_id',
                'orders.order_number',
                'competitions.id as competition_id',
                'competitions.title as competition_title'
            )
            ->orderByDesc('tickets.assigned_at')
            ->get()
            ->map(fn ($ticket) => [
                'encrypted_id' => Crypt::encryptString((string) $ticket->id),

                'ticket_number' => $ticket->ticket_number,
                'status' => $ticket->status,
                'assigned_at' => $ticket->assigned_at,

                'order_encrypted_id' => $ticket->order_id
                    ? Crypt::encryptString((string) $ticket->order_id)
                    : null,

                'order_number' => $ticket->order_number,

                'competition_encrypted_id' => Crypt::encryptString(
                    (string) $ticket->competition_id
                ),

                'competition_title' => $ticket->competition_title,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Customer Spending By Currency
        |--------------------------------------------------------------------------
        */

        $spendingByCurrency = DB::table('orders')
            ->where('user_id', $customer->id)
            ->where('payment_status', 'paid')
            ->select(
                'currency',
                DB::raw('SUM(total) as total_spent')
            )
            ->groupBy('currency')
            ->orderBy('currency')
            ->get()
            ->map(fn ($row) => [
                'currency' => $row->currency,
                'total' => number_format(
                    (float) $row->total_spent,
                    2,
                    '.',
                    ''
                ),
            ]);

        /*
        |--------------------------------------------------------------------------
        | Customer Statistics
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total_orders' => $orders->count(),

            'paid_orders' => $orders
                ->where('payment_status', 'paid')
                ->count(),

            'pending_orders' => $orders
                ->where('payment_status', 'pending')
                ->count(),

            'total_entries' => $tickets->count(),

            'total_payments' => $payments->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Admin/Customers/Show', [
            'customer' => [
                'encrypted_id' => Crypt::encryptString((string) $customer->id),

                'name' => $name,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number,
                'country' => $customer->country,
                'email_verified' => !empty($customer->email_verified_at),
                'created_at' => $customer->created_at,
            ],

            'stats' => $stats,

            'spendingByCurrency' => $spendingByCurrency,

            'orders' => $orders,

            'payments' => $payments,

            'tickets' => $tickets,
        ]);
    }
}
