<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Reporting Period
        |--------------------------------------------------------------------------
        */

        $period = $request->input('period', '30days');

        $allowedPeriods = [
            'today',
            '7days',
            '30days',
            'this_month',
            'this_year',
        ];

        if (!in_array($period, $allowedPeriods, true)) {
            $period = '30days';
        }

        $startDate = match ($period) {
            'today' => now()->startOfDay(),
            '7days' => now()->subDays(6)->startOfDay(),
            'this_month' => now()->startOfMonth(),
            'this_year' => now()->startOfYear(),
            default => now()->subDays(29)->startOfDay(),
        };

        $endDate = now()->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Paid Orders
        |--------------------------------------------------------------------------
        */

        $paidOrders = DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate]);

        // Revenue grouped by currency.
        $revenueByCurrency = (clone $paidOrders)
            ->select('currency')
            ->selectRaw('SUM(total) as revenue')
            ->groupBy('currency')
            ->get()
            ->map(fn ($row) => [
                'currency' => $row->currency,
                'revenue' => (float) $row->revenue,
            ]);

        $paidOrderCount = (clone $paidOrders)->count();

        // Average order value grouped by currency.
        $averageOrderValue = (clone $paidOrders)
            ->select('currency')
            ->selectRaw('AVG(total) as average_order_value')
            ->groupBy('currency')
            ->get()
            ->map(fn ($row) => [
                'currency' => $row->currency,
                'average_order_value' => (float) $row->average_order_value,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Ticket Sales
        |--------------------------------------------------------------------------
        */

        $ticketsSold = DB::table('competition_tickets as t')
            ->join('orders as o', 'o.id', '=', 't.order_id')
            ->where('t.status', 'assigned')
            ->where('o.payment_status', 'paid')
            ->whereBetween('o.paid_at', [$startDate, $endDate])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Customer Statistics
        |--------------------------------------------------------------------------
        */

        $newCustomers = DB::table('users')
            ->where('role', 'user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalCustomers = DB::table('users')
            ->where('role', 'user')
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Payment Status Breakdown
        |--------------------------------------------------------------------------
        */

        $paymentStatuses = DB::table('payments')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('status')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(fn ($row) => [
                'status' => $row->status,
                'total' => (int) $row->total,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Daily Revenue Trend
        |--------------------------------------------------------------------------
        */

        $revenueTrend = DB::table('orders')
            ->where('payment_status', 'paid')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->selectRaw('DATE(paid_at) as date, currency')
            ->selectRaw('SUM(total) as revenue')
            ->groupByRaw('DATE(paid_at), currency')
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'currency' => $row->currency,
                'revenue' => (float) $row->revenue,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Competition Capacity
        |--------------------------------------------------------------------------
        */

        $competitionCapacity = DB::table('competition_tickets')
            ->select('competition_id')
            ->selectRaw('COUNT(*) as total_tickets')
            ->selectRaw("
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END)
                as remaining_tickets
            ")
            ->groupBy('competition_id');

        /*
        |--------------------------------------------------------------------------
        | Competition Participation
        |--------------------------------------------------------------------------
        */

        $competitionParticipation = DB::table('competition_tickets as t')
            ->join('orders as o', 'o.id', '=', 't.order_id')
            ->where('t.status', 'assigned')
            ->where('o.payment_status', 'paid')
            ->select('t.competition_id')
            ->selectRaw('COUNT(*) as lifetime_tickets_sold')
            ->selectRaw('COUNT(DISTINCT t.user_id) as participants')
            ->groupBy('t.competition_id');

        /*
        |--------------------------------------------------------------------------
        | Competition Winners
        |--------------------------------------------------------------------------
        */

        $competitionWinners = DB::table('prize_winning_tickets')
            ->whereIn('winning_method', ['instant_win', 'draw'])
            ->where('status', '!=', 'pending')
            ->select('competition_id')
            ->selectRaw("
                SUM(
                    CASE WHEN winning_method = 'instant_win'
                    THEN 1 ELSE 0 END
                ) as instant_win_winners
            ")
            ->selectRaw("
                SUM(
                    CASE WHEN winning_method = 'draw'
                    THEN 1 ELSE 0 END
                ) as draw_winners
            ")
            ->groupBy('competition_id');

        /*
        |--------------------------------------------------------------------------
        | Competition Performance
        |--------------------------------------------------------------------------
        */

        $competitionPerformance = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')

            ->leftJoinSub(
                $competitionCapacity,
                'capacity',
                'capacity.competition_id',
                '=',
                'oi.competition_id'
            )

            ->leftJoinSub(
                $competitionParticipation,
                'participation',
                'participation.competition_id',
                '=',
                'oi.competition_id'
            )

            ->leftJoinSub(
                $competitionWinners,
                'winners',
                'winners.competition_id',
                '=',
                'oi.competition_id'
            )

            ->where('o.payment_status', 'paid')
            ->whereBetween('o.paid_at', [$startDate, $endDate])

            ->select(
                'oi.competition_id',
                'oi.competition_title',
                'oi.currency'
            )

            ->selectRaw('SUM(oi.quantity) as tickets_sold')
            ->selectRaw('SUM(oi.line_total) as revenue')

            ->selectRaw('COALESCE(capacity.total_tickets, 0) as total_tickets')
            ->selectRaw('COALESCE(capacity.remaining_tickets, 0) as remaining_tickets')

            ->selectRaw('COALESCE(participation.lifetime_tickets_sold, 0) as lifetime_tickets_sold')
            ->selectRaw('COALESCE(participation.participants, 0) as participants')

            ->selectRaw('COALESCE(winners.instant_win_winners, 0) as instant_win_winners')
            ->selectRaw('COALESCE(winners.draw_winners, 0) as draw_winners')

            ->groupBy(
                'oi.competition_id',
                'oi.competition_title',
                'oi.currency',
                'capacity.total_tickets',
                'capacity.remaining_tickets',
                'participation.lifetime_tickets_sold',
                'participation.participants',
                'winners.instant_win_winners',
                'winners.draw_winners'
            )

            ->orderByDesc('revenue')
            ->limit(10)
            ->get()

            ->map(function ($row) {
                $totalTickets = (int) $row->total_tickets;
                $lifetimeTicketsSold = (int) $row->lifetime_tickets_sold;

                return [
                    'competition_id' => $row->competition_id,
                    'title' => $row->competition_title,
                    'currency' => $row->currency,

                    // Selected reporting period.
                    'tickets_sold' => (int) $row->tickets_sold,
                    'revenue' => (float) $row->revenue,

                    // Current competition capacity.
                    'total_tickets' => $totalTickets,
                    'remaining_tickets' => (int) $row->remaining_tickets,

                    // Lifetime competition performance.
                    'lifetime_tickets_sold' => $lifetimeTicketsSold,
                    'participants' => (int) $row->participants,

                    'sales_progress' => $totalTickets > 0
                        ? round(($lifetimeTicketsSold / $totalTickets) * 100, 2)
                        : 0,

                    // Winner statistics.
                    'instant_win_winners' => (int) $row->instant_win_winners,
                    'draw_winners' => (int) $row->draw_winners,
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | Recent Paid Orders
        |--------------------------------------------------------------------------
        */

        $recentOrders = DB::table('orders as o')
            ->join('users as u', 'u.id', '=', 'o.user_id')
            ->where('o.payment_status', 'paid')
            ->whereBetween('o.paid_at', [$startDate, $endDate])

            ->select(
                'o.id',
                'o.order_number',
                'o.total',
                'o.currency',
                'o.paid_at',
                'u.first_name',
                'u.last_name',
                'u.email'
            )

            ->orderByDesc('o.paid_at')
            ->limit(8)
            ->get()

            ->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,

                'customer_name' => trim(
                    ($order->first_name ?? '') . ' ' .
                    ($order->last_name ?? '')
                ),

                'customer_email' => $order->email,
                'total' => (float) $order->total,
                'currency' => $order->currency,
                'paid_at' => $order->paid_at,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Return Reports Page
        |--------------------------------------------------------------------------
        */

        return Inertia::render('Admin/Reports/Index', [
            'statistics' => [
                'revenue_by_currency' => $revenueByCurrency,
                'average_order_value' => $averageOrderValue,
                'paid_orders' => $paidOrderCount,
                'tickets_sold' => $ticketsSold,
                'total_customers' => $totalCustomers,
                'new_customers' => $newCustomers,
            ],

            'payment_statuses' => $paymentStatuses,

            'revenue_trend' => $revenueTrend,

            'competition_performance' => $competitionPerformance,

            'recent_orders' => $recentOrders,

            'filters' => [
                'period' => $period,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
        ]);
    }
}
