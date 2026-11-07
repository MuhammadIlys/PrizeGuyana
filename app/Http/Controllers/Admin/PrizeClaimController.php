<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PrizeClaimController extends Controller
{
    /**
     * Display customer-submitted prize claims.
     */
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));
        $status = $request->query('status', '');

        $allowedStatuses = [
            'won',
            'claimed',
            'fulfilled',
            'cancelled',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $query = DB::table('prize_winning_tickets as pwt')
            ->join('competition_tickets as t', 't.id', '=', 'pwt.ticket_id')
            ->join('competition_prizes as p', 'p.id', '=', 'pwt.prize_id')
            ->join('my_competitions as c', 'c.id', '=', 'pwt.competition_id')
            ->join('users as u', 'u.id', '=', 'pwt.winner_user_id')
            ->whereIn('pwt.winning_method', ['instant_win', 'draw'])
            ->whereNotNull('pwt.claimed_at');

        // Search by customer, competition, prize or ticket number.
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('u.name', 'like', "%{$search}%")
                    ->orWhere('u.email', 'like', "%{$search}%")
                    ->orWhere('c.title', 'like', "%{$search}%")
                    ->orWhere('p.name', 'like', "%{$search}%")
                    ->orWhere('t.ticket_number', 'like', "%{$search}%");
            });
        }

        if ($status !== '') {
            $query->where('pwt.status', $status);
        }

        $claims = $query
            ->select(
                'pwt.id',
                'pwt.status',
                'pwt.winning_method',
                'pwt.won_at',
                'pwt.claimed_at',
                'pwt.fulfilled_at',
                'pwt.notes',
                'pwt.order_id',

                DB::raw("TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) as customer_name"),
                'u.id as customer_id',
                'u.email as customer_email',

                'c.title as competition_title',

                'p.name as prize_name',
                'p.prize_type',
                'p.prize_value',
                'p.currency',

                't.ticket_number'
            )
            ->orderByDesc('pwt.claimed_at')
            ->paginate(15)
            ->withQueryString();

        $claims->through(function ($claim) {
            return [
                'id' => Crypt::encryptString((string) $claim->id),

                'status' => $claim->status,
                'winning_method' => $claim->winning_method,

                'won_at' => $claim->won_at,
                'claimed_at' => $claim->claimed_at,
                'fulfilled_at' => $claim->fulfilled_at,

                'notes' => $claim->notes,
                'order_id' => $claim->order_id,

                'customer_name' => $claim->customer_name,
                'customer_encrypted_id' => Crypt::encryptString((string) $claim->customer_id),
                'customer_email' => $claim->customer_email,

                'competition_title' => $claim->competition_title,

                'prize_name' => $claim->prize_name,
                'prize_type' => $claim->prize_type,
                'prize_value' => $claim->prize_value,
                'currency' => $claim->currency,

                'ticket_number' => $claim->ticket_number,
            ];
        });

        // Claim statistics include both Instant Win and Scheduled Draw.
        $statistics = [
            'total' => DB::table('prize_winning_tickets')
                ->whereIn('winning_method', ['instant_win', 'draw'])
                ->whereNotNull('claimed_at')
                ->count(),

            'pending_review' => DB::table('prize_winning_tickets')
                ->whereIn('winning_method', ['instant_win', 'draw'])
                ->where('status', 'won')
                ->whereNotNull('claimed_at')
                ->count(),

            'approved' => DB::table('prize_winning_tickets')
                ->whereIn('winning_method', ['instant_win', 'draw'])
                ->where('status', 'claimed')
                ->whereNotNull('claimed_at')
                ->count(),

            'fulfilled' => DB::table('prize_winning_tickets')
                ->whereIn('winning_method', ['instant_win', 'draw'])
                ->where('status', 'fulfilled')
                ->whereNotNull('claimed_at')
                ->count(),

            'rejected' => DB::table('prize_winning_tickets')
                ->whereIn('winning_method', ['instant_win', 'draw'])
                ->where('status', 'cancelled')
                ->whereNotNull('claimed_at')
                ->count(),
        ];

        return Inertia::render('Admin/PrizeClaims/Index', [
            'claims' => $claims,
            'statistics' => $statistics,

            'filters' => [
                'search' => $search,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Display a single prize claim.
     */
    public function show(string $id)
    {
        try {
            $claimId = Crypt::decryptString($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }

        if (!ctype_digit((string) $claimId)) {
            abort(404);
        }

        $claim = DB::table('prize_winning_tickets as pwt')
            ->join('competition_tickets as t', 't.id', '=', 'pwt.ticket_id')
            ->join('competition_prizes as p', 'p.id', '=', 'pwt.prize_id')
            ->join('my_competitions as c', 'c.id', '=', 'pwt.competition_id')
            ->join('users as u', 'u.id', '=', 'pwt.winner_user_id')
            ->where('pwt.id', $claimId)
            ->whereIn('pwt.winning_method', ['instant_win', 'draw'])
            ->whereNotNull('pwt.claimed_at')
            ->select(
                'pwt.id',
                'pwt.status',
                'pwt.winning_method',
                'pwt.won_at',
                'pwt.claimed_at',
                'pwt.fulfilled_at',
                'pwt.notes',
                'pwt.order_id',

                'u.id as customer_id',
                DB::raw("TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) as customer_name"),
                'u.email as customer_email',

                'c.title as competition_title',

                'p.name as prize_name',
                'p.prize_type',
                'p.prize_value',
                'p.currency',

                't.ticket_number'
            )
            ->first();

        if (!$claim) {
            abort(404);
        }

        $claim = [
            'id' => Crypt::encryptString((string) $claim->id),
            'display_id' => $claimId,
            'status' => $claim->status,
            'winning_method' => $claim->winning_method,

            'won_at' => $claim->won_at,
            'claimed_at' => $claim->claimed_at,
            'fulfilled_at' => $claim->fulfilled_at,

            'notes' => $claim->notes,
            'order_id' => $claim->order_id,

            'customer_id' => $claim->customer_id,
            'customer_encrypted_id' => Crypt::encryptString((string) $claim->customer_id),
            'customer_name' => $claim->customer_name,
            'customer_email' => $claim->customer_email,

            'competition_title' => $claim->competition_title,

            'prize_name' => $claim->prize_name,
            'prize_type' => $claim->prize_type,
            'prize_value' => $claim->prize_value,
            'currency' => $claim->currency,

            'ticket_number' => $claim->ticket_number,
        ];

        return Inertia::render('Admin/PrizeClaims/Show', [
            'claim' => $claim,
        ]);
    }

    /**
     * Approve a customer-submitted prize claim.
     */
    public function approve(string $id)
    {
        try {
            $claimId = Crypt::decryptString($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }

        if (!ctype_digit((string) $claimId)) {
            abort(404);
        }

        DB::transaction(function () use ($claimId) {
            $claim = DB::table('prize_winning_tickets')
                ->where('id', $claimId)
                ->whereIn('winning_method', ['instant_win', 'draw'])
                ->lockForUpdate()
                ->first();

            if (
                !$claim ||
                $claim->status !== 'won' ||
                !$claim->claimed_at
            ) {
                abort(422, 'This claim cannot be approved.');
            }

            DB::table('prize_winning_tickets')
                ->where('id', $claimId)
                ->update([
                    'status' => 'claimed',
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Prize claim approved successfully.');
    }

    /**
     * Reject a customer-submitted prize claim.
     */
    public function reject(Request $request, string $id)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        try {
            $claimId = Crypt::decryptString($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }

        if (!ctype_digit((string) $claimId)) {
            abort(404);
        }

        DB::transaction(function () use ($claimId, $validated) {
            $claim = DB::table('prize_winning_tickets')
                ->where('id', $claimId)
                ->whereIn('winning_method', ['instant_win', 'draw'])
                ->lockForUpdate()
                ->first();

            if (
                !$claim ||
                $claim->status !== 'won' ||
                !$claim->claimed_at
            ) {
                abort(422, 'This claim cannot be rejected.');
            }

            DB::table('prize_winning_tickets')
                ->where('id', $claimId)
                ->update([
                    'status' => 'cancelled',
                    'rejection_reason' => trim($validated['reason']),
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Prize claim rejected successfully.');
    }

    /**
     * Mark an approved prize claim as fulfilled.
     */
    public function fulfill(string $id)
    {
        try {
            $claimId = Crypt::decryptString($id);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            abort(404);
        }

        if (!ctype_digit((string) $claimId)) {
            abort(404);
        }

        DB::transaction(function () use ($claimId) {
            $claim = DB::table('prize_winning_tickets')
                ->where('id', $claimId)
                ->whereIn('winning_method', ['instant_win', 'draw'])
                ->lockForUpdate()
                ->first();

            if (!$claim || $claim->status !== 'claimed') {
                abort(422, 'Only approved claims can be marked as fulfilled.');
            }

            DB::table('prize_winning_tickets')
                ->where('id', $claimId)
                ->update([
                    'status' => 'fulfilled',
                    'fulfilled_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->back()
            ->with('success', 'Prize marked as fulfilled successfully.');
    }
}