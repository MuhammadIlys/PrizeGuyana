<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Illuminate\Support\Facades\Crypt;

class AdminDashboardController extends Controller
{

    public function index()
    {
        $now = now();

        /*
    |--------------------------------------------------------------------------
    | Customers
    |--------------------------------------------------------------------------
    */

        $totalCustomers = DB::table('users')
            ->where('role', 'user')
            ->count();

        $recentCustomers = DB::table('users')
            ->where('role', 'user')
            ->select('id', 'name', 'email', 'created_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($customer) => [
                'encrypted_id' => Crypt::encryptString((string) $customer->id),
                'name' => $customer->name,
                'email' => $customer->email,
                'joined_at' => $customer->created_at
                    ? Carbon::parse($customer->created_at)->format('M d, Y')
                    : '—',
            ]);

        /*
    |--------------------------------------------------------------------------
    | Competitions
    |--------------------------------------------------------------------------
    */

        $totalCompetitions = DB::table('my_competitions')->count();

        $publishedCompetitions = DB::table('my_competitions')
            ->whereNotNull('published_at')
            ->count();

        $activeCompetitions = DB::table('my_competitions')
            ->whereNotNull('published_at')
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            })
            ->count();

        $upcomingCompetitions = DB::table('my_competitions')
            ->whereNotNull('published_at')
            ->where('starts_at', '>', $now)
            ->count();

        $completedCompetitions = DB::table('my_competitions')
            ->whereNotNull('published_at')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $now)
            ->count();

        /*
    |--------------------------------------------------------------------------
    | Tickets & Paid Entries
    |--------------------------------------------------------------------------
    |
    | available = ticket available for allocation
    | assigned  = ticket allocated to an order
    | paid entry = assigned ticket linked to a paid order
    |
    */

        $totalTickets = DB::table('competition_tickets')->count();

        $availableTickets = DB::table('competition_tickets')
            ->where('status', 'available')
            ->count();

        $assignedTickets = DB::table('competition_tickets')
            ->where('status', 'assigned')
            ->count();

        $paidEntries = DB::table('competition_tickets as tickets')
            ->join('orders', 'tickets.order_id', '=', 'orders.id')
            ->where('tickets.status', 'assigned')
            ->where('orders.payment_status', 'paid')
            ->count();

        /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */

        $totalOrders = DB::table('orders')->count();

        $pendingOrders = DB::table('orders')
            ->where('payment_status', 'pending')
            ->count();

        $paidOrders = DB::table('orders')
            ->where('payment_status', 'paid')
            ->count();

        $failedOrders = DB::table('orders')
            ->where('payment_status', 'failed')
            ->count();

        $recentOrders = DB::table('orders')
            ->leftJoin('users', 'orders.user_id', '=', 'users.id')
            ->select(
                'orders.id',
                'orders.order_number',
                'orders.total',
                'orders.currency',
                'orders.payment_status',
                'orders.created_at',
                'users.name as customer_name',
                'users.email as customer_email'
            )
            ->orderByDesc('orders.created_at')
            ->limit(5)
            ->get()
            ->map(fn($order) => [
                'encrypted_id' => Crypt::encryptString((string) $order->id),
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name ?? 'Unknown customer',
                'customer_email' => $order->customer_email ?? '',
                'total' => number_format((float) $order->total, 2),
                'currency' => $order->currency,
                'payment_status' => $order->payment_status,
                'date' => $order->created_at
                    ? Carbon::parse($order->created_at)->format('M d, Y')
                    : '—',
            ]);

        /*
    |--------------------------------------------------------------------------
    | Revenue - Keep Currencies Separate
    |--------------------------------------------------------------------------
    */

        $revenueByCurrency = DB::table('orders')
            ->where('payment_status', 'paid')
            ->select(
                'currency',
                DB::raw('SUM(total) as total_revenue')
            )
            ->groupBy('currency')
            ->orderBy('currency')
            ->get()
            ->map(fn($row) => [
                'currency' => $row->currency,
                'total' => number_format((float) $row->total_revenue, 2),
            ]);

        /*
    |--------------------------------------------------------------------------
    | Payments
    |--------------------------------------------------------------------------
    */

        $paymentCounts = DB::table('payments')
            ->select(
                'status',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(fn($payment) => [
                'status' => $payment->status,
                'total' => (int) $payment->total,
            ]);

        $totalPayments = DB::table('payments')->count();

        /*
    |--------------------------------------------------------------------------
    | Top Competitions
    |--------------------------------------------------------------------------
    |
    | allocated_entries = all assigned tickets
    | sold_entries      = assigned tickets linked to paid orders
    |
    */

        $topCompetitions = DB::table('my_competitions as competitions')
            ->leftJoin(
                'competition_tickets as tickets',
                'competitions.id',
                '=',
                'tickets.competition_id'
            )
            ->leftJoin(
                'orders',
                'tickets.order_id',
                '=',
                'orders.id'
            )
            ->select(
                'competitions.id',
                'competitions.title',
                'competitions.slug',
                'competitions.main_image',
                'competitions.total_entries',
                DB::raw("
                COUNT(
                    CASE
                        WHEN tickets.status = 'assigned'
                        THEN 1
                    END
                ) as allocated_entries
            "),
                DB::raw("
                COUNT(
                    CASE
                        WHEN tickets.status = 'assigned'
                        AND orders.payment_status = 'paid'
                        THEN 1
                    END
                ) as sold_entries
            "),
                DB::raw("
                COUNT(
                    CASE
                        WHEN tickets.status = 'available'
                        THEN 1
                    END
                ) as available_entries
            ")
            )
            ->groupBy(
                'competitions.id',
                'competitions.title',
                'competitions.slug',
                'competitions.main_image',
                'competitions.total_entries'
            )
            ->orderByDesc('sold_entries')
            ->limit(5)
            ->get()
            ->map(fn($competition) => [
                'encrypted_id' => Crypt::encryptString((string) $competition->id),
                'title' => $competition->title,
                'slug' => $competition->slug,
                'main_image' => $competition->main_image
                    ? '/storage/' . ltrim(
                        preg_replace('#^/?storage/#', '', $competition->main_image),
                        '/'
                    )
                    : null,
                'total_entries' => (int) $competition->total_entries,
                'sold_entries' => (int) $competition->sold_entries,
                'allocated_entries' => (int) $competition->allocated_entries,
                'available_entries' => (int) $competition->available_entries,
            ]);

        /*
    |--------------------------------------------------------------------------
    | Upcoming Draws
    |--------------------------------------------------------------------------
    */

        $upcomingDraws = DB::table('my_competitions as competitions')
            ->leftJoin(
                'competition_tickets as tickets',
                'competitions.id',
                '=',
                'tickets.competition_id'
            )
            ->leftJoin(
                'orders',
                'tickets.order_id',
                '=',
                'orders.id'
            )
            ->whereNotNull('competitions.published_at')
            ->whereNotNull('competitions.draw_at')
            ->where('competitions.draw_at', '>', $now)
            ->select(
                'competitions.id',
                'competitions.title',
                'competitions.draw_at',
                DB::raw("
                COUNT(
                    CASE
                        WHEN tickets.status = 'assigned'
                        AND orders.payment_status = 'paid'
                        THEN 1
                    END
                ) as sold_entries
            "),
                DB::raw("
                COUNT(
                    CASE
                        WHEN tickets.status = 'assigned'
                        THEN 1
                    END
                ) as allocated_entries
            ")
            )
            ->groupBy(
                'competitions.id',
                'competitions.title',
                'competitions.draw_at'
            )
            ->orderBy('competitions.draw_at')
            ->limit(5)
            ->get()
            ->map(fn($competition) => [
                'encrypted_id' => Crypt::encryptString((string) $competition->id),
                'title' => $competition->title,
                'draw_at' => Carbon::parse($competition->draw_at)
                    ->format('M d, Y - h:i A'),
                'sold_entries' => (int) $competition->sold_entries,
                'allocated_entries' => (int) $competition->allocated_entries,
            ]);

        /*
    |--------------------------------------------------------------------------
    | Dashboard Response
    |--------------------------------------------------------------------------
    */

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                // Customers
                'total_customers' => $totalCustomers,

                // Competitions
                'total_competitions' => $totalCompetitions,
                'published_competitions' => $publishedCompetitions,
                'active_competitions' => $activeCompetitions,
                'upcoming_competitions' => $upcomingCompetitions,
                'completed_competitions' => $completedCompetitions,

                // Tickets
                'total_tickets' => $totalTickets,
                'sold_tickets' => $paidEntries,
                'paid_entries' => $paidEntries,
                'available_tickets' => $availableTickets,
                'active_tickets' => $assignedTickets,
                'assigned_tickets' => $assignedTickets,

                // Orders
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'paid_orders' => $paidOrders,
                'failed_orders' => $failedOrders,

                // Payments
                'total_payments' => $totalPayments,
            ],

            'revenueByCurrency' => $revenueByCurrency,
            'paymentCounts' => $paymentCounts,
            'recentOrders' => $recentOrders,
            'recentCustomers' => $recentCustomers,
            'topCompetitions' => $topCompetitions,
            'upcomingDraws' => $upcomingDraws,
        ]);
    }
}
