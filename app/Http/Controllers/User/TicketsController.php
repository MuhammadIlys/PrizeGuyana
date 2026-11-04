<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TicketsController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = (int) $request->user()->id;

        $query = DB::table('competition_tickets')
            ->where('user_id', $userId);

        // Search by ticket number.
        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(
                'ticket_number',
                'like',
                '%' . $search . '%'
            );
        }

        // Filter by ticket status.
        $allowedStatuses = [
            'assigned',
            'available',
            'used',
            'cancelled',
        ];

        $status = $request->input('status');

        if (in_array($status, $allowedStatuses, true)) {
            $query->where('status', $status);
        }

        $tickets = $query
            ->orderByDesc('assigned_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($ticket) {
                return [
                    'id' => $ticket->id,
                    'competition_id' => $ticket->competition_id,
                    'ticket_number' => $ticket->ticket_number,
                    'status' => $ticket->status,
                    'assigned_at' => $ticket->assigned_at,
                    'order_id' => $ticket->order_id,
                ];
            });

        return Inertia::render('User/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $status ?? '',
            ],
        ]);
    }
}
