<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Requests\CompetitionStoreRequest;
use App\Http\Requests\CompetitionUpdateRequest;
use App\Models\CompetitionCategory;
use App\Models\CompetitionsModel;
use App\Models\CompetitionTermsVersion;
use App\Models\QualificationQuestion;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Inertia;


class MyCompetitionController extends Controller
{

    public function index(Request $request)
    {
        $competitions = CompetitionsModel::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->string('status')->toString());
            })
            ->latest()
            ->paginate(5)
            ->withQueryString()
            ->through(function ($competition) {
                $competition->encrypted_id = Crypt::encryptString((string) $competition->id);
                return $competition;
            });

        return Inertia::render('Admin/Competitions/Index', [
            'competitions' => $competitions,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status')->toString(),
            ],
        ]);
    }

    public function create()
    {
        $categories = CompetitionCategory::all();
        $terms = CompetitionTermsVersion::all();
        $questions = QualificationQuestion::all();
        return Inertia::render('Admin/Competitions/Create', [
            'categories' => $categories,
            'terms' => $terms,
            'questions' => $questions
        ]);
    }

    public function store(CompetitionStoreRequest $request)
    {
        // return $request;

        $data = $request->validated();

        $data['slug'] = Str::slug($data['title']);
        $data['description'] = $data['description'] ?? null;
        $data['main_image'] = $request->file('main_image')
            ->store('competitions/images', 'public');

        // Store video permanently
        $data['video_path'] = $request->hasFile('video_path')
            ? $request->file('video_path')->store('competitions/videos', 'public')
            : null;

        // Ensure it defaults to an empty array if nothing was selected
        $data['qualification_question_ids'] = $data['qualification_question_ids'] ?? [];

        $data['instant_win_enabled'] = $request->boolean('instant_win_enabled') ? 1 : 0;
        $data['qualification_enabled'] = $request->boolean('qualification_enabled') ? 1 : 0;

        $countryCodes = $data['country_codes'] ?? [];
        unset($data['country_codes']);

        $competition = CompetitionsModel::create($data);

        // $competition->countries()->sync($countryCodes);
        foreach ($countryCodes as $code) {
            DB::table('competition_country')->insert([
                'competition_id' => $competition->id,
                'country_code' => $code,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('admin.competitions.index')->with('success', 'Competition created successfully.');
    }

    public function show(string $id)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            abort(404);
        }

        $competition = CompetitionsModel::findOrFail($id);

        $competition->encrypted_id = Crypt::encryptString(
            (string) $competition->id
        );

        $category = CompetitionCategory::find($competition->category_id);

        // Get the selected qualification questions in their saved order
        $questionIds = $competition->qualification_question_ids ?? [];

        $questions = QualificationQuestion::whereIn('id', $questionIds)
            ->get()
            ->sortBy(fn($question) => array_search(
                $question->id,
                $questionIds
            ))
            ->values();

        $prizes = DB::table('competition_prizes')
            ->where('competition_id', $competition->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return Inertia::render('Admin/Competitions/Show', [
            'competition' => $competition,
            'category' => $category,
            'questions' => $questions,
            'prizes' => $prizes
        ]);
    }

    public function edit($id)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            abort(404);
        }

        $competition = CompetitionsModel::findOrFail($id);


        // Add encrypted ID for the Vue update route
        $competition->encrypted_id = Crypt::encryptString(
            (string) $competition->id
        );

        $categories = CompetitionCategory::all();
        $terms = CompetitionTermsVersion::all();
        $questions = QualificationQuestion::all();
        $competition->country_codes = DB::table('competition_country')
            ->where('competition_id', $competition->id)
            ->pluck('country_code')
            ->toArray();

        $competition->starts_at_local = $competition->starts_at
            ? Carbon::parse($competition->starts_at)->format('Y-m-d\TH:i')
            : '';

        $competition->ends_at_local = $competition->ends_at
            ? Carbon::parse($competition->ends_at)->format('Y-m-d\TH:i')
            : '';

        $competition->draw_at_local = $competition->draw_at
            ? Carbon::parse($competition->draw_at)->format('Y-m-d\TH:i')
            : '';

        return Inertia::render('Admin/Competitions/Edit', [
            'competition' => $competition,
            'categories' => $categories,
            'terms' => $terms,
            'questions' => $questions
        ]);
    }

    public function update(CompetitionUpdateRequest $request, string $id)
    {
        try {
            $id = Crypt::decryptString($id);
        } catch (DecryptException $e) {
            abort(404);
        }

        $competition = CompetitionsModel::findOrFail($id);

        $data = $request->validated();

        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('main_image')) {
            if ($competition->main_image) {
                Storage::disk('public')->delete($competition->main_image);
            }

            $data['main_image'] = $request->file('main_image')
                ->store('competitions/images', 'public');
        } else {
            unset($data['main_image']);
        }

        if ($request->hasFile('video')) {
            if ($competition->video_path) {
                Storage::disk('public')->delete($competition->video_path);
            }

            $data['video_path'] = $request->file('video')
                ->store('competitions/videos', 'public');
        } else {
            unset($data['video']);
            unset($data['video_path']);
        }

        $data['is_featured'] = $request->boolean('is_featured');
        $data['qualification_enabled'] = $request->boolean('qualification_enabled');
        $data['instant_win_enabled'] = $request->boolean('instant_win_enabled');
        // $data['status'] = $request->status;

        // if ($data['status'] === 'published') {
        //     if ($competition->status !== 'published') {
        //         $data['published_at'] = now();
        //     }
        // }

        $data['qualification_question_ids'] =
            $data['qualification_enabled'] ?? false
            ? ($data['qualification_question_ids'] ?? [])
            : [];

        $countryCodes = $data['country_codes'] ?? [];
        unset($data['country_codes']);

        $competition->update($data);
        // $competition->countries()->sync($countryCodes);

        DB::table('competition_country')
            ->where('competition_id', $competition->id)
            ->delete();

        foreach (array_unique($countryCodes) as $code) {
            DB::table('competition_country')->insert([
                'competition_id' => $competition->id,
                'country_code' => $code,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()
            ->route('admin.competitions.index')
            ->with('success', 'Competition updated successfully.');
    }

    public function destroy($id, Request $request)
    {
        $id = Crypt::decryptString($id);
        $competition = CompetitionsModel::findorfail($id);
        return $competition;
    }
}
