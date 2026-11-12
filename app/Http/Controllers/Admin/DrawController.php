<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DrawController extends Controller
{
    /**
     * Display competitions available for scheduled draw management.
     */
    public function index()
    {
        $drawCounts = DB::table('prize_winning_tickets')
            ->select(
                'competition_id',
                DB::raw('COUNT(*) as total_winners')
            )
            ->where('winning_method', 'draw')
            ->groupBy('competition_id');

        $drawPrizes = DB::table('competition_prizes')
            ->select(
                'competition_id',
                DB::raw('COUNT(*) as total_prizes'),
                DB::raw('SUM(quantity) as required_winners')
            )
            ->where('winning_method', 'draw')
            ->where('status', 'active')
            ->groupBy('competition_id');

        $competitions = DB::table('my_competitions as c')
            ->leftJoinSub($drawCounts, 'w', function ($join) {
                $join->on('c.id', '=', 'w.competition_id');
            })
            ->leftJoinSub($drawPrizes, 'p', function ($join) {
                $join->on('c.id', '=', 'p.competition_id');
            })
            ->select(
                'c.id',
                'c.title',
                'c.status',
                'c.ends_at',
                'c.completed_at',
                DB::raw('COALESCE(w.total_winners, 0) as total_winners'),
                DB::raw('COALESCE(p.total_prizes, 0) as total_prizes'),
                DB::raw('COALESCE(p.required_winners, 0) as required_winners')
            )
            ->where(function ($query) {
                $query->whereNotNull('p.total_prizes')
                    ->orWhereNotNull('w.total_winners');
            })
            ->orderByDesc('c.id')
            ->get()
            ->map(function ($competition) {
                return [
                    'encrypted_id' => Crypt::encryptString(
                        (string) $competition->id
                    ),
                    'title' => $competition->title,
                    'status' => $competition->status,
                    'ends_at' => $competition->ends_at,
                    'completed_at' => $competition->completed_at,
                    'total_winners' => (int) $competition->total_winners,
                    'total_prizes' => (int) $competition->total_prizes,
                    'required_winners' => (int) $competition->required_winners,
                ];
            });

        return Inertia::render('Admin/Draws/Index', [
            'competitions' => $competitions,
        ]);
    }

    /**
     * Display scheduled draw details for a competition.
     */
    public function show($competitionId)
    {
        $competitionId = $this->decryptId($competitionId);

        $competition = DB::table('my_competitions')
            ->where('id', $competitionId)
            ->first();

        abort_unless($competition, 404);

        $prizes = DB::table('competition_prizes as p')
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
                'p.quantity',
                'p.sort_order'
            )
            ->select(
                'p.id',
                'p.name',
                'p.description',
                'p.prize_type',
                'p.prize_value',
                'p.currency',
                'p.quantity',
                DB::raw('COUNT(pwt.id) as winner_count')
            )
            ->orderBy('p.sort_order')
            ->orderBy('p.id')
            ->get();

        $winners = DB::table('prize_winning_tickets as pwt')
            ->join('competition_tickets as t', 't.id', '=', 'pwt.ticket_id')
            ->join('competition_prizes as p', 'p.id', '=', 'pwt.prize_id')
            ->leftJoin('users as u', 'u.id', '=', 'pwt.winner_user_id')
            ->where('pwt.competition_id', $competitionId)
            ->where('pwt.winning_method', 'draw')
            ->select(
                'pwt.id',
                'pwt.status',
                'pwt.won_at',
                'pwt.claimed_at',
                'pwt.fulfilled_at',
                'pwt.order_id',
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
                    'prize_name' => $winner->prize_name,
                    'prize_type' => $winner->prize_type,
                    'prize_value' => $winner->prize_value,
                    'currency' => $winner->currency,
                    'ticket_number' => $winner->ticket_number,
                    'winner_name' => trim(
                        ($winner->first_name ?? '') . ' ' .
                        ($winner->last_name ?? '')
                    ) ?: 'Unknown Customer',
                    'order_id' => $winner->order_id,
                    'status' => $winner->status,
                    'won_at' => $winner->won_at,
                    'claimed_at' => $winner->claimed_at,
                    'fulfilled_at' => $winner->fulfilled_at,
                ];
            });

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

        return Inertia::render('Admin/Draws/Show', [
            'competition' => [
                'encrypted_id' => Crypt::encryptString(
                    (string) $competition->id
                ),
                'title' => $competition->title,
                'status' => $competition->status,
                'ends_at' => $competition->ends_at,
                'completed_at' => $competition->completed_at,
            ],
            'prizes' => $prizes,
            'winners' => $winners,
            'statistics' => [
                'total_prizes' => $prizes->count(),
                'required_winners' => (int) $prizes->sum('quantity'),
                'selected_winners' => $winners->count(),
                'eligible_tickets' => $eligibleTickets,
            ],
        ]);
    }

    /**
     * Execute the existing scheduled draw implementation.
     */
    public function execute($competitionId)
    {
        // Delegate to the existing implementation.
        // No draw-selection or notification logic is duplicated here.
        return app(CompetitionTicketController::class)
            ->finishDraw($competitionId);
    }

    /**
     * Decrypt an encrypted competition ID.
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
