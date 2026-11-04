<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompetitionCategories\StoreCompetitionCategoryRequest;
use App\Http\Requests\Admin\CompetitionCategories\UpdateCompetitionCategoryRequest;
use App\Models\CompetitionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CompetitionCategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = CompetitionCategory::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = $request->string('status')->toString();

                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/CompetitionCategories/Index', [
            'categories' => $categories,
            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/CompetitionCategories/Create');
    }

    public function store(StoreCompetitionCategoryRequest $request)
    {
        $validated = $request->validated();

        $validated['slug'] = $this->generateUniqueSlug(
            $validated['slug'] ?? $validated['name']
        );

        $validated['is_active'] = $request->boolean('is_active');

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        CompetitionCategory::create($validated);

        return redirect()
            ->route('admin.competition-categories.index')
            ->with(
                'success',
                'Competition category created successfully.'
            );
    }

    public function edit(CompetitionCategory $competitionCategory)
    {
        return Inertia::render('Admin/CompetitionCategories/Edit', [
            'category' => $competitionCategory,
        ]);
    }

    public function update(
        UpdateCompetitionCategoryRequest $request,
        CompetitionCategory $competitionCategory
    ) {
        $validated = $request->validated();

        $validated['slug'] = $this->generateUniqueSlugForUpdate(
            $validated['slug'] ?? $validated['name'],
            $competitionCategory
        );

        $validated['is_active'] = $request->boolean('is_active');

        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $competitionCategory->update($validated);

        return redirect()
            ->route('admin.competition-categories.index')
            ->with(
                'success',
                'Competition category updated successfully.'
            );
    }

    public function destroy(CompetitionCategory $competitionCategory)
    {
        $competitionCategory->delete();

        return redirect()
            ->route('admin.competition-categories.index')
            ->with(
                'success',
                'Competition category deleted successfully.'
            );
    }

    private function generateUniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'category';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (CompetitionCategory::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function generateUniqueSlugForUpdate(
        string $value,
        CompetitionCategory $category
    ): string {
        $baseSlug = Str::slug($value);

        if ($baseSlug === '') {
            $baseSlug = 'category';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            CompetitionCategory::where('slug', $slug)
            ->where('id', '!=', $category->id)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
