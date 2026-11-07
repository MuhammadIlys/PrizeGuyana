<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class Dashboard extends Controller
{


    public function index()
    {
        $userId = Auth::id();

        // -----------------------------------------
        // Order statistics
        // -----------------------------------------

        $ordersQuery = DB::table('orders')
            ->where('user_id', $userId);

        $totalOrders = (clone $ordersQuery)->count();

        $pendingOrders = (clone $ordersQuery)
            ->where('payment_status', 'pending')
            ->count();

        $completedOrders = (clone $ordersQuery)
            ->where('payment_status', 'paid')
            ->count();

        // Keep spending separate when orders use different currencies.
        $spendingByCurrency = DB::table('orders')
            ->where('user_id', $userId)
            ->where('payment_status', 'paid')
            ->select(
                'currency',
                DB::raw('SUM(total) as total_spent')
            )
            ->groupBy('currency')
            ->orderBy('currency')
            ->get();

        $totalSpent = $spendingByCurrency->isEmpty()
            ? '0.00'
            : $spendingByCurrency->map(
                fn($row) => number_format(
                    (float) $row->total_spent,
                    2
                ) . ' ' . $row->currency
            )->implode(' / ');

        // -----------------------------------------
        // Ticket statistics
        // -----------------------------------------

        $ticketsQuery = DB::table('competition_tickets')
            ->where('user_id', $userId)
            ->whereNotNull('order_id');

        $totalTickets = (clone $ticketsQuery)
            ->whereIn('status', ['assigned', 'used'])
            ->count();

        $activeTickets = (clone $ticketsQuery)
            ->where('status', 'assigned')
            ->count();

        // -----------------------------------------
        // Recent orders
        // -----------------------------------------

        $recentOrders = DB::table('orders')
            ->where('orders.user_id', $userId)
            ->leftJoin(
                'order_items',
                'orders.id',
                '=',
                'order_items.order_id'
            )
            ->select(
                'orders.id',
                'orders.order_number',
                'orders.created_at',
                'orders.total',
                'orders.currency',
                'orders.payment_status',
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as items')
            )
            ->groupBy(
                'orders.id',
                'orders.order_number',
                'orders.created_at',
                'orders.total',
                'orders.currency',
                'orders.payment_status'
            )
            ->orderByDesc('orders.created_at')
            ->limit(5)
            ->get()
            ->map(fn($order) => [
                'id' => $order->order_number,
                'date' => $order->created_at
                    ? \Carbon\Carbon::parse($order->created_at)->format('M d, Y')
                    : '—',
                'items' => (int) $order->items,
                'total' => number_format(
                    (float) $order->total,
                    2
                ) . ' ' . $order->currency,
                'status' => $order->payment_status,
            ]);

        // -----------------------------------------
        // Upcoming draws for entered competitions
        // -----------------------------------------

        $upcomingDraws = DB::table('competition_tickets as tickets')
            ->join(
                'my_competitions as competitions',
                'competitions.id',
                '=',
                'tickets.competition_id'
            )
            ->where('tickets.user_id', $userId)
            ->where('tickets.status', 'assigned')
            ->whereNotNull('tickets.order_id')
            ->whereNotNull('competitions.draw_at')
            ->where('competitions.draw_at', '>', now())
            ->select(
                'competitions.id',
                'competitions.title',
                'competitions.slug',
                'competitions.main_image',
                'competitions.draw_at',
                DB::raw('COUNT(tickets.id) as entries_count')
            )
            ->groupBy(
                'competitions.id',
                'competitions.title',
                'competitions.slug',
                'competitions.main_image',
                'competitions.draw_at'
            )
            ->orderBy('competitions.draw_at')
            ->limit(3)
            ->get()
            ->map(fn($competition) => [
                'id' => $competition->id,
                'title' => $competition->title,
                'slug' => $competition->slug,
                'main_image' => $competition->main_image
                    ? '/storage/' . ltrim(
                        preg_replace(
                            '#^/?storage/#',
                            '',
                            $competition->main_image
                        ),
                        '/'
                    )
                    : null,
                'draw_at' => \Carbon\Carbon::parse(
                    $competition->draw_at
                )->format('M d, Y - h:i A'),
                'entries_count' => (int) $competition->entries_count,
            ]);

        // -----------------------------------------
        // Customer winning tickets
        // -----------------------------------------

        $winningTickets = DB::table('prize_winning_tickets as winning')
            ->join(
                'competition_tickets as tickets',
                'tickets.id',
                '=',
                'winning.ticket_id'
            )
            ->join(
                'competition_prizes as prizes',
                'prizes.id',
                '=',
                'winning.prize_id'
            )
            ->join(
                'my_competitions as competitions',
                'competitions.id',
                '=',
                'winning.competition_id'
            )
            ->where('winning.winner_user_id', $userId)
            ->select(
                'winning.id',
                'winning.winning_method',
                'winning.status as claim_status',
                'winning.won_at',
                'winning.claimed_at',
                'winning.fulfilled_at',
                'tickets.ticket_number',
                'prizes.name as prize_name',
                'prizes.image_path as prize_image',
                'competitions.title as competition_title'
            )
            ->orderByDesc('winning.won_at')
            ->get()
            ->map(fn($win) => [
                'id' => encrypt((string) $win->id),
                'winning_method' => $win->winning_method,
                'claim_status' => $win->claim_status,
                'won_at' => $win->won_at,
                'claimed_at' => $win->claimed_at,
                'fulfilled_at' => $win->fulfilled_at,
                'ticket_number' => $win->ticket_number,
                'prize_name' => $win->prize_name,
                'prize_image' => $win->prize_image,
                'competition_title' => $win->competition_title,
            ]);

        return Inertia::render('User/Dashboard', [
            'stats' => [
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'total_spent' => $totalSpent,
                'total_tickets' => $totalTickets,
                'active_tickets' => $activeTickets,
            ],
            'recentOrders' => $recentOrders,
            'upcomingDraws' => $upcomingDraws,
            'winningTickets' => $winningTickets,
        ]);
    }
}
