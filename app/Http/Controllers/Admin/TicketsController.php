<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TicketsController extends Controller
{
    /**
     * Display all competition tickets.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'competition' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:available,assigned,unavailable'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Ticket Query
        |--------------------------------------------------------------------------
        */

        $query = DB::table('competition_tickets')
            ->leftJoin(
                'my_competitions',
                'competition_tickets.competition_id',
                '=',
                'my_competitions.id'
            )
            ->leftJoin(
                'users',
                'competition_tickets.user_id',
                '=',
                'users.id'
            )
            ->leftJoin(
                'orders',
                'competition_tickets.order_id',
                '=',
                'orders.id'
            )
            ->select(
                'competition_tickets.id',
                'competition_tickets.competition_id',
                'competition_tickets.ticket_number',
                'competition_tickets.status',
                'competition_tickets.user_id',
                'competition_tickets.order_id',
                'competition_tickets.assigned_at',
                'competition_tickets.created_at',
                'my_competitions.title as competition_title',
                'users.name as customer_name',
                'users.email as customer_email',
                'orders.order_number',
                'orders.payment_status'
            );

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($q) use ($search) {
                $q->where(
                    'competition_tickets.ticket_number',
                    'like',
                    "%{$search}%"
                )
                ->orWhere('users.name', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%")
                ->orWhere('orders.order_number', 'like', "%{$search}%");
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Competition Filter
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['competition'])) {
            $query->where(
                'competition_tickets.competition_id',
                $filters['competition']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['status'])) {
            $query->where(
                'competition_tickets.status',
                $filters['status']
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $tickets = $query
            ->orderByDesc('competition_tickets.created_at')
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Overview Statistics
        |--------------------------------------------------------------------------
        */

        $ticketStats = DB::table('competition_tickets')
            ->selectRaw('COUNT(*) as total_tickets')
            ->selectRaw("
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END)
                as available_tickets
            ")
            ->selectRaw("
                SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END)
                as assigned_tickets
            ")
            ->selectRaw("
                SUM(CASE WHEN status = 'unavailable' THEN 1 ELSE 0 END)
                as unavailable_tickets
            ")
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Competition Filter Options
        |--------------------------------------------------------------------------
        */

        $competitions = DB::table('my_competitions')
            ->select('id', 'title')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Admin/Tickets/Index', [
            'tickets' => $tickets,

            'stats' => [
                'total_tickets' => (int) ($ticketStats->total_tickets ?? 0),
                'available_tickets' => (int) ($ticketStats->available_tickets ?? 0),
                'assigned_tickets' => (int) ($ticketStats->assigned_tickets ?? 0),
                'unavailable_tickets' => (int) ($ticketStats->unavailable_tickets ?? 0),
            ],

            'competitions' => $competitions,

            'filters' => [
                'search' => $filters['search'] ?? '',
                'competition' => $filters['competition'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
        ]);
    }

    /**
     * Display a specific ticket.
     */
    public function show($id)
    {
        $ticket = DB::table('competition_tickets')
            ->leftJoin(
                'my_competitions',
                'competition_tickets.competition_id',
                '=',
                'my_competitions.id'
            )
            ->leftJoin(
                'users',
                'competition_tickets.user_id',
                '=',
                'users.id'
            )
            ->leftJoin(
                'orders',
                'competition_tickets.order_id',
                '=',
                'orders.id'
            )
            ->where('competition_tickets.id', $id)
            ->select(
                'competition_tickets.id',
                'competition_tickets.competition_id',
                'competition_tickets.ticket_number',
                'competition_tickets.status',
                'competition_tickets.user_id',
                'competition_tickets.order_id',
                'competition_tickets.assigned_at',
                'competition_tickets.created_at',
                'competition_tickets.updated_at',
                'my_competitions.title as competition_title',
                'my_competitions.slug as competition_slug',
                'users.name as customer_name',
                'users.email as customer_email',
                'orders.order_number',
                'orders.status as order_status',
                'orders.payment_status',
                'orders.total as order_total',
                'orders.currency as order_currency'
            )
            ->first();

        abort_if(!$ticket, 404);

        return Inertia::render('Admin/Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }
}