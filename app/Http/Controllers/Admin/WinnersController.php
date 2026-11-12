<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WinnersController extends Controller
{
    /**
     * Display all actual competition winners.
     */
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));
        $method = $request->query('method', '');
        $status = $request->query('status', '');

        if (!in_array($method, ['', 'instant_win', 'draw'], true)) {
            $method = '';
        }

        if (!in_array($status, ['', 'pending', 'under_review', 'approved', 'rejected', 'fulfilled'], true)) {
            $status = '';
        }

        $baseQuery = DB::table('prize_winning_tickets as pwt')
            ->join('competition_tickets as t', 't.id', '=', 'pwt.ticket_id')
            ->join('competition_prizes as p', 'p.id', '=', 'pwt.prize_id')
            ->join('my_competitions as c', 'c.id', '=', 'pwt.competition_id')
            ->join('users as u', 'u.id', '=', 'pwt.winner_user_id')
            ->whereIn('pwt.winning_method', ['instant_win', 'draw']);

        // Only actual winners, not pending Instant Win allocations.
        if ($search !== '') {
            $baseQuery->where(function ($query) use ($search) {
                $query->where('u.first_name', 'like', "%{$search}%")
                    ->orWhere('u.last_name', 'like', "%{$search}%")
                    ->orWhere('u.email', 'like', "%{$search}%")
                    ->orWhere('c.title', 'like', "%{$search}%")
                    ->orWhere('p.name', 'like', "%{$search}%")
                    ->orWhere('t.ticket_number', 'like', "%{$search}%");
            });
        }

        if ($method !== '') {
            $baseQuery->where('pwt.winning_method', $method);
        }

        if ($status !== '') {
            switch ($status) {
                case 'pending':
                    $baseQuery->where('pwt.status', 'won')
                        ->whereNull('pwt.claimed_at');
                    break;

                case 'under_review':
                    $baseQuery->where('pwt.status', 'won')
                        ->whereNotNull('pwt.claimed_at');
                    break;

                case 'approved':
                    $baseQuery->where('pwt.status', 'claimed');
                    break;

                case 'rejected':
                    $baseQuery->where('pwt.status', 'cancelled');
                    break;

                case 'fulfilled':
                    $baseQuery->where('pwt.status', 'fulfilled');
                    break;
            }
        }

        $winners = $baseQuery
            ->select(
                'pwt.id',
                'pwt.status',
                'pwt.winning_method',
                'pwt.won_at',
                'pwt.claimed_at',
                'pwt.fulfilled_at',
                'pwt.order_id',
                'pwt.rejection_reason',
                DB::raw("TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) as customer_name"),
                'u.email as customer_email',
                'c.title as competition_title',
                'p.name as prize_name',
                'p.prize_type',
                'p.prize_value',
                'p.currency',
                't.ticket_number'
            )
            ->orderByDesc('pwt.won_at')
            ->orderByDesc('pwt.id')
            ->paginate(15)
            ->withQueryString();

        $winners->through(function ($winner) {
            return [
                'id' => Crypt::encryptString((string) $winner->id),
                'customer_name' => $winner->customer_name ?: 'Unknown Customer',
                'customer_email' => $winner->customer_email,
                'competition_title' => $winner->competition_title,
                'prize_name' => $winner->prize_name,
                'prize_type' => $winner->prize_type,
                'prize_value' => $winner->prize_value,
                'currency' => $winner->currency,
                'ticket_number' => $winner->ticket_number,
                'order_id' => $winner->order_id,
                'winning_method' => $winner->winning_method,
                'status' => $winner->status,
                'claim_status' => $this->claimStatus($winner),
                'won_at' => $winner->won_at,
                'claimed_at' => $winner->claimed_at,
                'fulfilled_at' => $winner->fulfilled_at,
            ];
        });

        $statisticsQuery = DB::table('prize_winning_tickets')
            ->whereIn('winning_method', ['instant_win', 'draw'])
            ->whereNotNull('winner_user_id');

        $statistics = [
            'total' => (clone $statisticsQuery)->count(),

            'instant_win' => (clone $statisticsQuery)
                ->where('winning_method', 'instant_win')
                ->count(),

            'draw' => (clone $statisticsQuery)
                ->where('winning_method', 'draw')
                ->count(),

            'pending_claims' => (clone $statisticsQuery)
                ->where('status', 'won')
                ->whereNull('claimed_at')
                ->count(),

            'under_review' => (clone $statisticsQuery)
                ->where('status', 'won')
                ->whereNotNull('claimed_at')
                ->count(),

            'fulfilled' => (clone $statisticsQuery)
                ->where('status', 'fulfilled')
                ->count(),
        ];

        return Inertia::render('Admin/Winners/Index', [
            'winners' => $winners,
            'statistics' => $statistics,
            'filters' => [
                'search' => $search,
                'method' => $method,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Display an individual winner's complete information.
     */
    public function show(string $id)
    {
        try {
            $winnerId = Crypt::decryptString($id);
        } catch (DecryptException $exception) {
            abort(404);
        }

        if (!ctype_digit($winnerId) || (int) $winnerId < 1) {
            abort(404);
        }

        $winner = DB::table('prize_winning_tickets as pwt')
            ->join('competition_tickets as t', 't.id', '=', 'pwt.ticket_id')
            ->join('competition_prizes as p', 'p.id', '=', 'pwt.prize_id')
            ->join('my_competitions as c', 'c.id', '=', 'pwt.competition_id')
            ->join('users as u', 'u.id', '=', 'pwt.winner_user_id')
            ->leftJoin('orders as o', 'o.id', '=', 'pwt.order_id')
            ->where('pwt.id', $winnerId)
            ->whereIn('pwt.winning_method', ['instant_win', 'draw'])
            ->select(
                'pwt.id',
                'pwt.competition_id',
                'pwt.prize_id',
                'pwt.ticket_id',
                'pwt.winner_user_id',
                'pwt.order_id',
                'pwt.winning_method',
                'pwt.status',
                'pwt.won_at',
                'pwt.claimed_at',
                'pwt.fulfilled_at',
                'pwt.notes',
                'pwt.rejection_reason',

                'u.first_name',
                'u.last_name',
                'u.email',
                'u.phone_number',
                'u.country',

                'c.title as competition_title',
                'c.status as competition_status',
                'c.currency as competition_currency',

                'p.name as prize_name',
                'p.description as prize_description',
                'p.image_path as prize_image',
                'p.prize_type',
                'p.prize_value',
                'p.currency as prize_currency',

                't.ticket_number',

                'o.id as order_id'
            )
            ->first();

        abort_unless($winner, 404);

        return Inertia::render('Admin/Winners/Show', [
            'winner' => [
                'id' => Crypt::encryptString((string) $winner->id),
                'display_id' => $winner->id,

                'customer' => [
                    'id' => Crypt::encryptString((string) $winner->winner_user_id),
                    'name' => trim(($winner->first_name ?? '') . ' ' . ($winner->last_name ?? '')) ?: 'Unknown Customer',
                    'email' => $winner->email,
                    'phone' => $winner->phone_number,
                    'country' => $winner->country,
                ],

                'competition' => [
                    'id' => Crypt::encryptString((string) $winner->competition_id),
                    'title' => $winner->competition_title,
                    'status' => $winner->competition_status,
                    'currency' => $winner->competition_currency,
                ],

                'prize' => [
                    'name' => $winner->prize_name,
                    'description' => $winner->prize_description,
                    'image' => $winner->prize_image
                        ? asset('storage/' . $winner->prize_image)
                        : null,
                    'type' => $winner->prize_type,
                    'value' => $winner->prize_value,
                    'currency' => $winner->prize_currency,
                ],

                'ticket' => [
                    'id' => $winner->ticket_id,
                    'number' => $winner->ticket_number,
                ],

                'order_id' => $winner->order_id,
                'winning_method' => $winner->winning_method,
                'status' => $winner->status,
                'claim_status' => $this->claimStatus($winner),
                'won_at' => $winner->won_at,
                'claimed_at' => $winner->claimed_at,
                'fulfilled_at' => $winner->fulfilled_at,
                'rejection_reason' => $winner->rejection_reason,
                'notes' => $winner->notes,
            ],
        ]);
    }

    /**
     * Return a readable claim status without changing database values.
     */
    private function claimStatus(object $winner): string
    {
        if ($winner->status === 'fulfilled') {
            return 'fulfilled';
        }

        if ($winner->status === 'cancelled') {
            return 'rejected';
        }

        if ($winner->status === 'claimed') {
            return 'approved';
        }

        if ($winner->claimed_at) {
            return 'under_review';
        }

        return 'pending';
    }
}
