<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PrizesStoreRequest;
use App\Http\Requests\UpdateCompetitionPrizeRequest;
use App\Models\CompetitionPrize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CompetitionPrizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        $prizes = DB::table('competition_prizes')
            ->leftJoin('my_competitions', 'competition_prizes.competition_id', '=', 'my_competitions.id')
            ->select(
                'competition_prizes.*',
                'my_competitions.title as competition_title'
            )
            ->orderByDesc('competition_prizes.id')
            ->get();

        return Inertia::render('Admin/CompetitionPrizes/Index', [
            'prizes' => $prizes,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $competitions = DB::table('my_competitions')
            ->select('id', 'title')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Admin/CompetitionPrizes/Create', [
            'competitions' => $competitions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(PrizesStoreRequest $request)
    {
        $validated = $request->validated();

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')
                ->store('images/prizes', 'public');
        }

        DB::table('competition_prizes')->insert([
            'competition_id' => $validated['competition_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'prize_role' => $validated['prize_role'],
            'prize_type' => $validated['prize_type'],
            'image_path' => $imagePath,
            'prize_value' => $validated['prize_value'] ?? null,
            'currency' => strtoupper($validated['currency']),
            'quantity' => $validated['quantity'],
            'winning_method' => $validated['winning_method'],
            'status' => $validated['status'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.competition-prizes.index')
            ->with('success', 'Prize created successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */

    public function edit(string $id)
    {
        $prize = DB::table('competition_prizes')
            ->where('id', $id)
            ->first();

        abort_if(!$prize, 404);

        $competitions = DB::table('my_competitions')
            ->select('id', 'title')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Admin/CompetitionPrizes/Edit', [
            'prize' => $prize,
            'competitions' => $competitions,
        ]);
    }


    /**
     * Update the specified resource in storage.
     */

    public function update(UpdateCompetitionPrizeRequest $request, string $id)
    {
        $prize = DB::table('competition_prizes')
            ->where('id', $id)
            ->first();

        abort_if(!$prize, 404);

        $validated = $request->validated();
        $imagePath = $prize->image_path;

        if ($request->hasFile('image')) {
            $newImagePath = $request->file('image')
                ->store('images/prizes', 'public');

            if ($newImagePath === false) {
                return back()
                    ->withInput()
                    ->withErrors(['image' => 'Failed to upload the prize image.']);
            }

            $imagePath = $newImagePath;
        }

        DB::table('competition_prizes')
            ->where('id', $id)
            ->update([
                'competition_id' => $validated['competition_id'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'prize_role' => $validated['prize_role'],
                'prize_type' => $validated['prize_type'],
                'image_path' => $imagePath,
                'prize_value' => $validated['prize_value'] ?? null,
                'currency' => strtoupper($validated['currency']),
                'quantity' => $validated['quantity'],
                'winning_method' => $validated['winning_method'],
                'status' => $validated['status'],
                'sort_order' => $validated['sort_order'] ?? 0,
                'updated_at' => now(),
            ]);

        if ($request->hasFile('image') && $prize->image_path) {
            Storage::disk('public')->delete($prize->image_path);
        }

        return redirect()
            ->route('admin.competition-prizes.index')
            ->with('success', 'Prize updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     */

    public function destroy(string $id)
    {
        $prize = DB::table('competition_prizes')
            ->where('id', $id)
            ->first();

        abort_if(!$prize, 404);

        // TODO: Check winning records before enabling deletion.
        // Replace 'prize_winners' with your actual winning records table
        // once that table exists.

        DB::table('competition_prizes')
            ->where('id', $id)
            ->delete();

        if ($prize->image_path) {
            Storage::disk('public')->delete($prize->image_path);
        }

        return redirect()
            ->route('admin.competition-prizes.index')
            ->with('success', 'Prize deleted successfully.');
    }
}
