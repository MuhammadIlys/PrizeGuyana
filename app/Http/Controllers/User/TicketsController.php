<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        // Fetch Instant Win and Scheduled Draw winning records
        // for tickets on the current page.
        $ticketIds = $tickets->getCollection()->pluck('id')->all();

        $winningRecords = DB::table('prize_winning_tickets as winning')
            ->join('competition_prizes as prizes', 'prizes.id', '=', 'winning.prize_id')
            ->whereIn('winning.ticket_id', $ticketIds)
            ->whereIn('winning.winning_method', ['instant_win', 'draw'])
            ->select(
                'winning.id as winning_id',
                'winning.ticket_id',
                'winning.winning_method',
                'winning.status as winning_status',
                'prizes.name as prize_name',
                'prizes.image_path as prize_image'
            )
            ->get()
            ->keyBy('ticket_id');

        // Add competition and winning information without changing existing ticket data.
        $tickets->getCollection()->transform(function ($ticket) use ($winningRecords) {
            $winning = $winningRecords->get($ticket['id']);

            $competition = DB::table('my_competitions')
                ->where('id', $ticket['competition_id'])
                ->value('title');

            $ticket['competition_title'] = $competition;

            // General winning information.
            $ticket['is_winner'] = (bool) $winning;

            // Preserve existing Instant Win property.
            $ticket['is_instant_winner'] =
                ($winning->winning_method ?? null) === 'instant_win';

            // New Scheduled Draw property.
            $ticket['is_draw_winner'] =
                ($winning->winning_method ?? null) === 'draw';

            $ticket['winning_id'] = !empty($winning->winning_id)
                ? encrypt((string) $winning->winning_id)
                : null;

            $ticket['winning_method'] = $winning->winning_method ?? null;
            $ticket['winning_status'] = $winning->winning_status ?? null;
            $ticket['prize_name'] = $winning->prize_name ?? null;
            $ticket['prize_image'] = $winning->prize_image ?? null;

            return $ticket;
        });

        return Inertia::render('User/Tickets/Index', [
            'tickets' => $tickets,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $status ?? '',
            ],
        ]);
    }

    public function showPrize(string $winningTicket): Response
    {
        try {
            $winningTicketId = decrypt($winningTicket);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }

        $userId = Auth::user()->id;

        $prize = DB::table('prize_winning_tickets as winning')
            ->join('competition_tickets as tickets', 'tickets.id', '=', 'winning.ticket_id')
            ->join('competition_prizes as prizes', 'prizes.id', '=', 'winning.prize_id')
            ->join('my_competitions as competitions', 'competitions.id', '=', 'winning.competition_id')
            ->where('winning.id', $winningTicketId)
            ->whereIn('winning.winning_method', ['instant_win', 'draw'])
            ->where('tickets.user_id', $userId)
            ->where('tickets.status', 'assigned')
            ->select(
                'winning.id',
                'winning.winning_method',
                'winning.status as claim_status',
                'winning.winner_user_id',
                'winning.order_id',
                'winning.won_at',
                'winning.claimed_at',
                'winning.fulfilled_at',
                'winning.rejection_reason',
                'tickets.ticket_number',
                'prizes.name as prize_name',
                'prizes.description as prize_description',
                'prizes.image_path as prize_image',
                'competitions.title as competition_title',
            )
            ->first();

        abort_unless($prize, 404);

        $prize->winning_id = encrypt((string) $prize->id);

        return Inertia::render('User/Prizes/Show', [
            'prize' => $prize,
        ]);
    }


    public function submitPrizeClaim(string $winningTicket)
    {
        try {
            $winningTicketId = decrypt($winningTicket);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }

        $userId = (int) Auth::id();

        $submitted = DB::transaction(function () use ($winningTicketId, $userId) {

            $winning = DB::table('prize_winning_tickets as winning')
                ->join(
                    'competition_tickets as tickets',
                    'tickets.id',
                    '=',
                    'winning.ticket_id'
                )
                ->where('winning.id', $winningTicketId)
                ->whereIn('winning.winning_method', ['instant_win', 'draw'])
                ->where('tickets.user_id', $userId)
                ->where('tickets.status', 'assigned')
                ->select(
                    'winning.id',
                    'winning.status',
                    'winning.claimed_at'
                )
                ->lockForUpdate()
                ->first();

            if (!$winning || $winning->status !== 'won' || $winning->claimed_at !== null) {
                return false;
            }

            DB::table('prize_winning_tickets')
                ->where('id', $winningTicketId)
                ->where('status', 'won')
                ->whereNull('claimed_at')
                ->update([
                    'claimed_at' => now(),
                    'updated_at' => now(),
                ]);

            return true;
        });

        if (!$submitted) {
            return back()->with(
                'error',
                'This prize cannot be claimed again or is no longer available for claiming.'
            );
        }

        /*
     * Notify administrators after successful claim submission.
     */
        try {
            $claimDetails = DB::table('prize_winning_tickets as winning')
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
                ->where('winning.id', $winningTicketId)
                ->where('tickets.user_id', $userId)
                ->select(
                    'prizes.name as prize_name',
                    'tickets.ticket_number',
                    'competitions.title as competition_title'
                )
                ->first();

            if ($claimDetails) {
                $customer = Auth::user();

                app(NotificationService::class)->notifyAdmins(
                    'New Prize Claim Submitted',
                    "{$customer->first_name} {$customer->last_name} has submitted a claim for {$claimDetails->prize_name} (Ticket #{$claimDetails->ticket_number}) in {$claimDetails->competition_title}. Please review the claim.",
                    route('admin.prize-claims.show', [
                        'id' => encrypt((string) $winningTicketId),
                    ]),
                    'prize_claim'
                );
            }
        } catch (\Throwable $exception) {
            // Notification failure must not affect the submitted claim.
            report($exception);
        }

        return redirect()
            ->route('user.prizes.show', [
                'winningTicket' => $winningTicket,
            ])
            ->with('success', 'Your prize claim has been submitted successfully.');
    }
}
