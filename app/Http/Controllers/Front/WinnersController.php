<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WinnersController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));

        $query = DB::table('prize_winning_tickets as pwt')
            ->join('competition_tickets as t', 't.id', '=', 'pwt.ticket_id')
            ->join('users as u', 'u.id', '=', 'pwt.winner_user_id')
            ->join('my_competitions as c', 'c.id', '=', 'pwt.competition_id')
            ->leftJoin('competition_prizes as p', 'p.id', '=', 'pwt.prize_id')
            ->whereNotNull('pwt.winner_user_id')
            ->whereNotNull('pwt.won_at');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('u.first_name', 'like', "%{$search}%")
                    ->orWhere('u.last_name', 'like', "%{$search}%")
                    ->orWhere('t.ticket_number', 'like', "%{$search}%")
                    ->orWhere('c.title', 'like', "%{$search}%")
                    ->orWhere('p.name', 'like', "%{$search}%");
            });
        }

        $winners = $query
            ->select(
                'pwt.id',
                'pwt.won_at',
                'pwt.status',
                't.ticket_number',
                'u.first_name',
                'u.last_name',
                'c.title as competition_title',
                'p.name as prize_name',
                'p.description as prize_description',
                'p.prize_type',
                'p.prize_value',
                'p.currency'
            )
            ->orderByDesc('pwt.won_at')
            ->paginate(12)
            ->withQueryString()
            ->through(function ($winner) {
                $winner->winner_name = trim(
                    ($winner->first_name ?? '') . ' ' .
                    ($winner->last_name ?? '')
                );

                $winner->initials = strtoupper(
                    substr($winner->first_name ?? '', 0, 1) .
                    substr($winner->last_name ?? '', 0, 1)
                );

                return $winner;
            });

        return Inertia::render('Front/Winners', [
            'winners' => $winners,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
}
