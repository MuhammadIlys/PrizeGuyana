<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompetitionTerm;
use App\Models\CompetitionTermsVersion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

use App\Http\Requests\Admin\CompetitionTerms\StoreCompetitionTermsVersionRequest;
use App\Http\Requests\Admin\CompetitionTerms\UpdateCompetitionTermsVersionRequest;

class CompetitionTermsVersionController extends Controller
{
    public function index(Request $request)
    {
        $query = CompetitionTermsVersion::query();

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();

            $query->where(function ($q) use ($search) {
                $q->where('version', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->boolean('status')
            );
        }

        $terms = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/CompetitionTerms/Index', [
            'terms' => $terms,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ],
        ]);
    }

    public function create()
    {
        $termsVersions = CompetitionTermsVersion::query()
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get([
                'id',
                'version',
                'title',
            ]);

        return Inertia::render('Admin/CompetitionTerms/Create', [
            'termsVersions' => $termsVersions,
        ]);
    }

    public function store(StoreCompetitionTermsVersionRequest $request)
    {
        $validated = $request->validated();

        $validated['is_active'] = $request->boolean('is_active');

        CompetitionTermsVersion::create($validated);

        return redirect()
            ->route('admin.competition-terms.index')
            ->with(
                'success',
                'Terms version created successfully.'
            );
    }

    public function edit(CompetitionTermsVersion $competitionTerm)
    {
        return Inertia::render(
            'Admin/CompetitionTerms/Edit',
            [
                'term' => $competitionTerm,
            ]
        );
    }

    public function update(
        UpdateCompetitionTermsVersionRequest $request,
        CompetitionTermsVersion $competitionTerm
    ) {
        $validated = $request->validated();

        $validated['is_active'] = $request->boolean('is_active');

        $competitionTerm->update($validated);

        return redirect()
            ->route('admin.competition-terms.index')
            ->with(
                'success',
                'Terms & Conditions updated successfully.'
            );
    }
    public function destroy(CompetitionTermsVersion $competitionTerm)
    {
        $competitionTerm->delete();

        return redirect()
            ->route('admin.competition-terms.index')
            ->with(
                'success',
                'Terms & Conditions deleted successfully.'
            );
    }
}
