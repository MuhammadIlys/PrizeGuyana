<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CompetitionStatusController extends Controller
{
    public function toggle($competitionId)
    {
        $competition = DB::table('my_competitions')
            ->where('id', $competitionId)
            ->first();

        if (!$competition) {
            return back()->with('error', 'Competition not found.');
        }

        // Published → Active
        if ($competition->status === 'published') {
            DB::table('my_competitions')
                ->where('id', $competitionId)
                ->where('status', 'published')
                ->update([
                    'status' => 'active',
                    'starts_at' => now(),
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                'Competition activated successfully.'
            );
        }

        // Active → Published (deactivated)
        if ($competition->status === 'active') {
            DB::table('my_competitions')
                ->where('id', $competitionId)
                ->where('status', 'active')
                ->update([
                    'status' => 'published',
                    'updated_at' => now(),
                ]);

            return back()->with(
                'success',
                'Competition deactivated successfully.'
            );
        }

        return back()->with(
            'error',
            'Only published competitions can be activated.'
        );
    }
}
