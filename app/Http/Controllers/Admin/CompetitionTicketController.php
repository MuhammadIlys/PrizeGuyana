<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CompetitionTicketController extends Controller
{
    public function index()
    {
        $ticketCounts = DB::table('competition_tickets')
            ->select(
                'competition_id',
                DB::raw('COUNT(*) as generated_tickets'),
                DB::raw("SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_tickets"),
                DB::raw("SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned_tickets")
            )
            ->groupBy('competition_id');

        $competitions = DB::table('my_competitions as c')
            ->leftJoinSub($ticketCounts, 't', function ($join) {
                $join->on('c.id', '=', 't.competition_id');
            })
            ->select(
                'c.id',
                'c.title',
                'c.status',
                'c.total_entries',
                DB::raw('COALESCE(t.generated_tickets, 0) as generated_tickets'),
                DB::raw('COALESCE(t.available_tickets, 0) as available_tickets'),
                DB::raw('COALESCE(t.assigned_tickets, 0) as assigned_tickets')
            )
            ->orderByDesc('c.id')
            ->get()
            ->map(function ($competition) {
                return [
                    'encrypted_id' => Crypt::encryptString((string) $competition->id),
                    'title' => $competition->title,
                    'status' => $competition->status,
                    'total_entries' => $competition->total_entries,
                    'generated_tickets' => $competition->generated_tickets,
                    'available_tickets' => $competition->available_tickets,
                    'assigned_tickets' => $competition->assigned_tickets,
                ];
            });

        return Inertia::render('Admin/CompetitionTickets/Index', [
            'competitions' => $competitions,
        ]);
    }

    /**
     * Display competition tickets and Instant Win allocation information.
     */


    public function show(Request $request, $competitionId)
    {
        $competitionId = $this->decryptId($competitionId);

        $competition = DB::table('my_competitions')
            ->where('id', $competitionId)
            ->first();

        abort_unless($competition, 404);

        $search = trim($request->query('search', ''));
        $status = $request->query('status', '');

        if (!in_array($status, ['', 'available', 'assigned', 'unavailable'], true)) {
            $status = '';
        }

        // Ticket records with Instant Win / Draw allocation details
        $query = DB::table('competition_tickets')
            ->leftJoin('prize_winning_tickets', function ($join) {
                $join->on(
                    'prize_winning_tickets.ticket_id',
                    '=',
                    'competition_tickets.id'
                )->on(
                    'prize_winning_tickets.competition_id',
                    '=',
                    'competition_tickets.competition_id'
                );
            })
            ->leftJoin(
                'competition_prizes',
                'competition_prizes.id',
                '=',
                'prize_winning_tickets.prize_id'
            )
            ->where('competition_tickets.competition_id', $competitionId);

        if ($search !== '') {
            $query->where(
                'competition_tickets.ticket_number',
                'like',
                '%' . $search . '%'
            );
        }

        if ($status !== '') {
            $query->where('competition_tickets.status', $status);
        }

        $tickets = $query
            ->select(
                'competition_tickets.id',
                'competition_tickets.ticket_number',
                'competition_tickets.status',
                'competition_tickets.user_id',
                'competition_tickets.order_id',
                'competition_tickets.assigned_at',
                'competition_prizes.name as winning_prize_name',
                'prize_winning_tickets.winning_method',
                'prize_winning_tickets.status as winning_status'
            )
            ->orderBy('competition_tickets.id')
            ->paginate(50)
            ->withQueryString();

        $tickets->through(function ($ticket) {
            return [
                'id' => $ticket->id,
                'ticket_number' => $ticket->ticket_number,
                'status' => $ticket->status,
                'user_id' => $ticket->user_id,
                'order_id' => $ticket->order_id,
                'assigned_at' => $ticket->assigned_at,

                // Winning information
                'winning_prize_name' => $ticket->winning_prize_name,
                'winning_method' => $ticket->winning_method,
                'winning_status' => $ticket->winning_status,
            ];
        });

        // Active Instant Win prizes and their allocation counts
        $prizes = DB::table('competition_prizes as p')
            ->leftJoin('prize_winning_tickets as pwt', function ($join) {
                $join->on('pwt.prize_id', '=', 'p.id')
                    ->where('pwt.winning_method', '=', 'instant_win');
            })
            ->where('p.competition_id', $competitionId)
            ->where('p.winning_method', 'instant_win')
            ->where('p.status', 'active')
            ->groupBy(
                'p.id',
                'p.name',
                'p.description',
                'p.prize_type',
                'p.prize_value',
                'p.currency',
                'p.quantity'
            )
            ->select(
                'p.id',
                'p.name',
                'p.description',
                'p.prize_type',
                'p.prize_value',
                'p.currency',
                'p.quantity',
                DB::raw('COUNT(pwt.id) as allocated_quantity')
            )
            ->orderBy('p.id')
            ->get()
            ->map(function ($prize) {
                return [
                    'id' => $prize->id,
                    'name' => $prize->name,
                    'description' => $prize->description,
                    'prize_type' => $prize->prize_type,
                    'prize_value' => $prize->prize_value,
                    'currency' => $prize->currency,
                    'quantity' => (int) $prize->quantity,
                    'allocated_quantity' => (int) $prize->allocated_quantity,
                ];
            });

        // Active Scheduled Draw prizes and their winner counts
        $scheduledDrawPrizes = DB::table('competition_prizes as p')
            ->leftJoin('prize_winning_tickets as pwt', function ($join) {
                $join->on('pwt.prize_id', '=', 'p.id')
                    ->where('pwt.winning_method', '=', 'draw');
            })
            ->where('p.competition_id', $competitionId)
            ->where('p.winning_method', 'draw')
            ->where('p.status', 'active')
            ->groupBy(
                'p.id',
                'p.name',
                'p.description',
                'p.prize_type',
                'p.prize_value',
                'p.currency',
                'p.quantity'
            )
            ->select(
                'p.id',
                'p.name',
                'p.description',
                'p.prize_type',
                'p.prize_value',
                'p.currency',
                'p.quantity',
                DB::raw('COUNT(pwt.id) as allocated_quantity')
            )
            ->orderBy('p.sort_order')
            ->get()
            ->map(function ($prize) {
                return [
                    'id' => $prize->id,
                    'name' => $prize->name,
                    'description' => $prize->description,
                    'prize_type' => $prize->prize_type,
                    'prize_value' => $prize->prize_value,
                    'currency' => $prize->currency,
                    'quantity' => (int) $prize->quantity,
                    'allocated_quantity' => (int) $prize->allocated_quantity,
                ];
            });

        // Scheduled Draw winners
        $scheduledDrawWinners = DB::table('prize_winning_tickets as pwt')
            ->join('competition_tickets as t', 't.id', '=', 'pwt.ticket_id')
            ->join('competition_prizes as p', 'p.id', '=', 'pwt.prize_id')
            ->leftJoin('users as u', 'u.id', '=', 'pwt.winner_user_id')
            ->where('pwt.competition_id', $competitionId)
            ->where('pwt.winning_method', 'draw')
            ->select(
                'pwt.id',
                'pwt.prize_id',
                'pwt.ticket_id',
                'pwt.winner_user_id',
                'pwt.order_id',
                'pwt.status',
                'pwt.won_at',
                'pwt.claimed_at',
                'pwt.fulfilled_at',
                'pwt.rejection_reason',
                'p.name as prize_name',
                'p.prize_type',
                'p.prize_value',
                'p.currency',
                't.ticket_number',
                'u.first_name',
                'u.last_name'
            )
            ->orderBy('p.sort_order')
            ->orderBy('pwt.id')
            ->get()
            ->map(function ($winner) {
                return [
                    'id' => $winner->id,
                    'prize_id' => $winner->prize_id,
                    'prize_name' => $winner->prize_name,
                    'prize_type' => $winner->prize_type,
                    'prize_value' => $winner->prize_value,
                    'currency' => $winner->currency,
                    'ticket_number' => $winner->ticket_number,
                    'winner_user_id' => $winner->winner_user_id,
                    'winner_name' => trim(
                        ($winner->first_name ?? '') . ' ' .
                            ($winner->last_name ?? '')
                    ) ?: 'Unknown Customer',
                    'order_id' => $winner->order_id,
                    'status' => $winner->status,
                    'won_at' => $winner->won_at,
                    'claimed_at' => $winner->claimed_at,
                    'fulfilled_at' => $winner->fulfilled_at,
                    'rejection_reason' => $winner->rejection_reason,
                ];
            });

        // Check whether Instant Win allocations exist
        $allocationExists = DB::table('prize_winning_tickets')
            ->where('competition_id', $competitionId)
            ->where('winning_method', 'instant_win')
            ->exists();

        // Ticket statistics
        $ticketStatistics = DB::table('competition_tickets')
            ->where('competition_id', $competitionId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available")
            ->selectRaw("SUM(CASE WHEN status = 'assigned' THEN 1 ELSE 0 END) as assigned")
            ->first();

        return Inertia::render('Admin/CompetitionTickets/Show', [
            'competition' => [
                'encrypted_id' => Crypt::encryptString((string) $competition->id),
                'title' => $competition->title,
                'status' => $competition->status,
                'total_entries' => $competition->total_entries,
                'instant_win_enabled' => (bool) $competition->instant_win_enabled,
            ],

            'tickets' => $tickets,

            'filters' => [
                'search' => $search,
                'status' => $status,
            ],

            // Instant Win prizes
            'prizes' => $prizes,

            // Scheduled Draw prizes
            'scheduledDrawPrizes' => $scheduledDrawPrizes,

            // Scheduled Draw winner records
            'scheduledDrawWinners' => $scheduledDrawWinners,

            'allocation' => [
                'exists' => $allocationExists,
                'total_tickets' => (int) ($ticketStatistics->total ?? 0),
                'available_tickets' => (int) ($ticketStatistics->available ?? 0),
                'assigned_tickets' => (int) ($ticketStatistics->assigned ?? 0),
            ],
        ]);
    }



    /**
     * Generate a random preview of Instant Win ticket allocations.
     * Nothing is saved to the database by this action.
     */
    public function previewAllocation($competitionId)
    {
        $competitionId = $this->decryptId($competitionId);

        $result = DB::transaction(function () use ($competitionId) {
            $competition = DB::table('my_competitions')
                ->where('id', $competitionId)
                ->lockForUpdate()
                ->first();

            if (!$competition) {
                return [
                    'success' => false,
                    'message' => 'Competition not found.',
                ];
            }

            $this->validateAllocationEligibility($competition);

            $alreadyAllocated = DB::table('prize_winning_tickets')
                ->where('competition_id', $competitionId)
                ->where('winning_method', 'instant_win')
                ->exists();

            if ($alreadyAllocated) {
                return [
                    'success' => false,
                    'message' => 'Instant Win tickets have already been allocated.',
                ];
            }

            $prizes = DB::table('competition_prizes')
                ->where('competition_id', $competitionId)
                ->where('winning_method', 'instant_win')
                ->where('status', 'active')
                ->orderBy('id')
                ->get();

            if ($prizes->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No active Instant Win prizes were found.',
                ];
            }

            $requiredTickets = 0;

            foreach ($prizes as $prize) {
                if ((int) $prize->quantity < 1) {
                    return [
                        'success' => false,
                        'message' => "Invalid quantity configured for prize: {$prize->name}.",
                    ];
                }

                $requiredTickets += (int) $prize->quantity;
            }

            $generatedTickets = DB::table('competition_tickets')
                ->where('competition_id', $competitionId)
                ->count();

            if ($generatedTickets !== (int) $competition->total_entries) {
                return [
                    'success' => false,
                    'message' => 'The generated ticket count does not match the competition capacity.',
                ];
            }

            $availableTickets = DB::table('competition_tickets as t')
                ->where('t.competition_id', $competitionId)
                ->where('t.status', 'available')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('prize_winning_tickets as pwt')
                        ->whereColumn('pwt.ticket_id', 't.id');
                })
                ->count();

            if ($availableTickets < $requiredTickets) {
                return [
                    'success' => false,
                    'message' => "Not enough available tickets. Required: {$requiredTickets}; available: {$availableTickets}.",
                ];
            }

            $selectedTickets = $this->secureRandomTicketSample(
                $competitionId,
                $requiredTickets
            );

            if (count($selectedTickets) !== $requiredTickets) {
                return [
                    'success' => false,
                    'message' => 'Unable to select enough unique tickets. Please try again.',
                ];
            }

            $allocations = [];
            $ticketOffset = 0;

            foreach ($prizes as $prize) {
                $quantity = (int) $prize->quantity;

                $prizeTickets = array_slice(
                    $selectedTickets,
                    $ticketOffset,
                    $quantity
                );

                $ticketOffset += $quantity;

                $allocations[] = [
                    'prize_id' => (int) $prize->id,
                    'prize_name' => $prize->name,
                    'quantity' => $quantity,
                    'ticket_ids' => array_column($prizeTickets, 'id'),
                    'tickets' => $prizeTickets,
                ];
            }

            return [
                'success' => true,
                'message' => 'Instant Win allocation preview generated successfully.',
                'allocations' => $allocations,
                'total_allocations' => $requiredTickets,
            ];
        }, 3);

        return response()->json(
            $result,
            $result['success'] ? 200 : 422
        );
    }

    /**
     * Save the confirmed Instant Win ticket allocations.
     */
    public function allocateWinningTickets(Request $request, $competitionId)
    {
        $competitionId = $this->decryptId($competitionId);
        $competition = DB::table('my_competitions')->where('id', $competitionId)->first();
        if ($competition) {
            DB::table('my_competitions')
                ->where('id', $competitionId)
                ->update(['status' => 'published']);
        }
        $validated = $request->validate([
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.prize_id' => ['required', 'integer', 'min:1'],
            'allocations.*.ticket_ids' => ['required', 'array', 'min:1'],
            'allocations.*.ticket_ids.*' => ['required', 'integer', 'min:1'],
        ]);

        $result = DB::transaction(function () use ($competitionId, $validated) {
            $competition = DB::table('my_competitions')
                ->where('id', $competitionId)
                ->lockForUpdate()
                ->first();

            if (!$competition) {
                return [
                    'success' => false,
                    'message' => 'Competition not found.',
                ];
            }

            $this->validateAllocationEligibility($competition);

            $alreadyAllocated = DB::table('prize_winning_tickets')
                ->where('competition_id', $competitionId)
                ->where('winning_method', 'instant_win')
                ->exists();

            if ($alreadyAllocated) {
                return [
                    'success' => false,
                    'message' => 'Instant Win tickets have already been allocated. Existing allocations cannot be replaced.',
                ];
            }

            $prizes = DB::table('competition_prizes')
                ->where('competition_id', $competitionId)
                ->where('winning_method', 'instant_win')
                ->where('status', 'active')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($prizes->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No active Instant Win prizes were found.',
                ];
            }

            $submittedAllocations = collect($validated['allocations']);

            // Every active Instant Win prize must be included exactly once.
            if ($submittedAllocations->count() !== $prizes->count()) {
                return [
                    'success' => false,
                    'message' => 'The submitted allocation does not match the active Instant Win prizes.',
                ];
            }

            $allTicketIds = [];
            $allocationRows = [];
            $now = now();

            foreach ($submittedAllocations as $allocation) {
                $prizeId = (int) $allocation['prize_id'];

                if (!$prizes->has($prizeId)) {
                    return [
                        'success' => false,
                        'message' => 'An invalid or inactive prize was submitted.',
                    ];
                }

                $prize = $prizes->get($prizeId);
                $ticketIds = array_map('intval', $allocation['ticket_ids']);

                if (count($ticketIds) !== (int) $prize->quantity) {
                    return [
                        'success' => false,
                        'message' => "The ticket quantity for {$prize->name} does not match its configured prize quantity.",
                    ];
                }

                if (count($ticketIds) !== count(array_unique($ticketIds))) {
                    return [
                        'success' => false,
                        'message' => "Duplicate tickets were submitted for {$prize->name}.",
                    ];
                }

                foreach ($ticketIds as $ticketId) {
                    $allTicketIds[] = $ticketId;

                    $allocationRows[] = [
                        'competition_id' => $competitionId,
                        'prize_id' => $prizeId,
                        'ticket_id' => $ticketId,
                        'winning_method' => 'instant_win',
                        'status' => 'pending',
                        'winner_user_id' => null,
                        'order_id' => null,
                        'won_at' => null,
                        'claimed_at' => null,
                        'fulfilled_at' => null,
                        'notes' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Ensure every prize is included and no ticket is repeated
            // across different prizes.
            if (
                count($allTicketIds) !== count(array_unique($allTicketIds))
                || count($allTicketIds) !== array_sum(
                    $prizes->pluck('quantity')->map(fn($quantity) => (int) $quantity)->all()
                )
            ) {
                return [
                    'success' => false,
                    'message' => 'Duplicate or incomplete ticket allocations were submitted.',
                ];
            }

            // Lock all submitted tickets and verify their competition/status.
            $tickets = DB::table('competition_tickets')
                ->where('competition_id', $competitionId)
                ->whereIn('id', $allTicketIds)
                ->where('status', 'available')
                ->lockForUpdate()
                ->get(['id']);

            if ($tickets->count() !== count($allTicketIds)) {
                return [
                    'success' => false,
                    'message' => 'Some selected tickets are no longer available. Generate a new preview.',
                ];
            }

            // A ticket must never win more than one prize in the competition.
            $existingTicketWins = DB::table('prize_winning_tickets')
                ->whereIn('ticket_id', $allTicketIds)
                ->exists();

            if ($existingTicketWins) {
                return [
                    'success' => false,
                    'message' => 'One or more selected tickets have already been allocated to a prize.',
                ];
            }

            foreach (array_chunk($allocationRows, 500) as $chunk) {
                DB::table('prize_winning_tickets')->insert($chunk);
            }

            return [
                'success' => true,
                'message' => count($allocationRows) . ' Instant Win tickets allocated successfully.',
            ];
        }, 3);

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Execute and permanently save the scheduled competition draw.
     */
    public function finishDraw($competitionId)
    {
        $competitionId = $this->decryptId($competitionId);

        $result = DB::transaction(function () use ($competitionId) {

            $competition = DB::table('my_competitions')
                ->where('id', $competitionId)
                ->lockForUpdate()
                ->first();

            if (!$competition) {
                return [
                    'success' => false,
                    'message' => 'Competition not found.',
                ];
            }

            // Prevent duplicate draw execution.
            if ($competition->status === 'completed') {
                return [
                    'success' => false,
                    'message' => 'This competition has already been completed.',
                ];
            }

            if ($competition->status !== 'active') {
                return [
                    'success' => false,
                    'message' => 'Only active competitions can be completed.',
                ];
            }

            // Get active scheduled draw prizes.
            $prizes = DB::table('competition_prizes')
                ->where('competition_id', $competitionId)
                ->where('winning_method', 'draw')
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($prizes->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No active scheduled draw prizes were found.',
                ];
            }

            foreach ($prizes as $prize) {
                if ((int) $prize->quantity < 1) {
                    return [
                        'success' => false,
                        'message' => "Invalid quantity for prize: {$prize->name}.",
                    ];
                }
            }

            $requiredWinners = (int) $prizes->sum('quantity');

            // Count eligible purchased tickets.
            // Tickets that have already won ANY prize are excluded.
            $eligibleTickets = DB::table('competition_tickets as t')
                ->where('t.competition_id', $competitionId)
                ->where('t.status', 'assigned')
                ->whereNotNull('t.user_id')
                ->whereNotNull('t.order_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('prize_winning_tickets as pwt')
                        ->whereColumn('pwt.ticket_id', 't.id');
                })
                ->count();

            if ($eligibleTickets < $requiredWinners) {
                return [
                    'success' => false,
                    'message' => "Not enough eligible tickets. Required: {$requiredWinners}; available: {$eligibleTickets}.",
                ];
            }

            // Randomly select unique eligible tickets.
            $selectedTickets = DB::table('competition_tickets as t')
                ->where('t.competition_id', $competitionId)
                ->where('t.status', 'assigned')
                ->whereNotNull('t.user_id')
                ->whereNotNull('t.order_id')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('prize_winning_tickets as pwt')
                        ->whereColumn('pwt.ticket_id', 't.id');
                })
                ->inRandomOrder()
                ->limit($requiredWinners)
                ->lockForUpdate()
                ->get([
                    't.id',
                    't.user_id',
                    't.order_id',
                ]);

            if ($selectedTickets->count() !== $requiredWinners) {
                return [
                    'success' => false,
                    'message' => 'Unable to select enough eligible tickets. Please try again.',
                ];
            }

            $allocationRows = [];
            $ticketOffset = 0;
            $now = now();

            // Assign randomly selected tickets to configured prizes.
            foreach ($prizes as $prize) {

                $quantity = (int) $prize->quantity;

                $prizeTickets = $selectedTickets->slice(
                    $ticketOffset,
                    $quantity
                );

                $ticketOffset += $quantity;

                foreach ($prizeTickets as $ticket) {
                    $allocationRows[] = [
                        'competition_id' => $competitionId,
                        'prize_id' => $prize->id,
                        'ticket_id' => $ticket->id,
                        'winning_method' => 'draw',
                        'status' => 'won',
                        'winner_user_id' => $ticket->user_id,
                        'order_id' => $ticket->order_id,
                        'won_at' => $now,
                        'claimed_at' => null,
                        'fulfilled_at' => null,
                        'notes' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            // Save all scheduled draw winners.
            DB::table('prize_winning_tickets')->insert($allocationRows);

            // Complete the competition at the moment of execution.
            DB::table('my_competitions')
                ->where('id', $competitionId)
                ->update([
                    'status' => 'completed',
                    'completed_at' => $now,
                    'updated_at' => $now,
                ]);

            return [
                'success' => true,
                'message' => count($allocationRows) . ' scheduled draw winners selected successfully.',
            ];
        }, 3);

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Generate competition tickets.
     * This method remains separate from prize allocation.
     */
    public function generate($competitionId)
    {
        $competitionId = $this->decryptId($competitionId);

        $result = DB::transaction(function () use ($competitionId) {
            $competition = DB::table('my_competitions')
                ->where('id', $competitionId)
                ->lockForUpdate()
                ->first();

            if (!$competition) {
                return [
                    'success' => false,
                    'message' => 'Competition not found.',
                ];
            }

            $totalEntries = (int) $competition->total_entries;

            if ($totalEntries < 1) {
                return [
                    'success' => false,
                    'message' => 'Competition must have at least one entry.',
                ];
            }

            $existingTickets = DB::table('competition_tickets')
                ->where('competition_id', $competitionId)
                ->exists();

            if ($existingTickets) {
                return [
                    'success' => false,
                    'message' => 'Tickets have already been generated for this competition.',
                ];
            }

            $now = now();

            for ($start = 1; $start <= $totalEntries; $start += 500) {
                $end = min($start + 499, $totalEntries);
                $rows = [];

                for ($number = $start; $number <= $end; $number++) {
                    $rows[] = [
                        'competition_id' => $competitionId,
                        'ticket_number' => 'PG-' . str_pad(
                            (string) $number,
                            6,
                            '0',
                            STR_PAD_LEFT
                        ),
                        'status' => 'available',
                        'user_id' => null,
                        'order_id' => null,
                        'assigned_at' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                DB::table('competition_tickets')->insert($rows);
            }

            return [
                'success' => true,
                'message' => "{$totalEntries} tickets generated successfully.",
            ];
        }, 3);

        return back()->with(
            $result['success'] ? 'success' : 'error',
            $result['message']
        );
    }

    /**
     * Ensure the competition is eligible for Instant Win allocation.
     */
    private function validateAllocationEligibility(object $competition): void
    {
        if (!(bool) $competition->instant_win_enabled) {
            abort(422, 'Instant Win is not enabled for this competition.');
        }

        if (in_array($competition->status, ['active', 'completed', 'cancelled'], true)) {
            abort(422, 'Instant Win tickets can only be allocated before the competition becomes active.');
        }
    }

    /**
     * Cryptographically secure random sampling without loading
     * every available ticket into memory.
     *
     * Reservoir sampling keeps memory usage proportional to the
     * number of winning tickets required.
     */
    private function secureRandomTicketSample(int $competitionId, int $needed): array
    {
        if ($needed < 1) {
            return [];
        }

        $selected = [];
        $seen = 0;

        $tickets = DB::table('competition_tickets as t')
            ->where('t.competition_id', $competitionId)
            ->where('t.status', 'available')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('prize_winning_tickets as pwt')
                    ->whereColumn('pwt.ticket_id', 't.id');
            })
            ->select('t.id', 't.ticket_number')
            ->orderBy('t.id')
            ->cursor();

        foreach ($tickets as $ticket) {
            $seen++;

            $item = [
                'id' => (int) $ticket->id,
                'ticket_number' => $ticket->ticket_number,
            ];

            if (count($selected) < $needed) {
                $selected[] = $item;
                continue;
            }

            $position = random_int(1, $seen);

            if ($position <= $needed) {
                $selected[$position - 1] = $item;
            }
        }

        // Secure Fisher-Yates shuffle.
        for ($i = count($selected) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);

            [$selected[$i], $selected[$j]] = [
                $selected[$j],
                $selected[$i],
            ];
        }

        return array_values($selected);
    }

    /**
     * Decrypt an encrypted database ID.
     */
    private function decryptId(string $encryptedId): int
    {
        try {
            $id = Crypt::decryptString($encryptedId);
        } catch (DecryptException $exception) {
            abort(404);
        }

        if (!ctype_digit($id) || (int) $id < 1) {
            abort(404);
        }

        return (int) $id;
    }
}
