<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class EntriesController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = (int) $request->user()->id;

        $query = DB::table('competition_tickets as tickets')
            ->join(
                'my_competitions as competitions',
                'competitions.id',
                '=',
                'tickets.competition_id'
            )
            ->where('tickets.user_id', $userId)
            ->whereNotNull('tickets.order_id')
            ->where('tickets.status', '!=', 'cancelled')
            ->select(
                'competitions.id as competition_id',
                'competitions.title',
                'competitions.slug',
                'competitions.main_image',
                'competitions.ends_at',
                'competitions.status as competition_status',
                DB::raw('COUNT(tickets.id) as entries_count'),
                DB::raw(
                    "SUM(CASE WHEN tickets.status = 'assigned' THEN 1 ELSE 0 END) as active_entries"
                ),
                DB::raw(
                    "SUM(CASE WHEN tickets.status = 'used' THEN 1 ELSE 0 END) as used_entries"
                )
            )
            ->groupBy(
                'competitions.id',
                'competitions.title',
                'competitions.slug',
                'competitions.main_image',
                'competitions.ends_at',
                'competitions.status'
            );

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(
                'competitions.title',
                'like',
                '%' . $search . '%'
            );
        }

        $entries = $query
            ->orderBy('competitions.ends_at')
            ->paginate(10)
            ->withQueryString();

        $entries->through(function ($entry) {
            $entry->main_image = $entry->main_image
                ? '/storage/' . ltrim(
                    preg_replace('#^/?storage/#', '', $entry->main_image),
                    '/'
                )
                : null;

            return $entry;
        });


        return Inertia::render('User/Entries/Index', [
            'entries' => $entries,
            'filters' => [
                'search' => $request->input('search', ''),
            ],
        ]);
    }
}
