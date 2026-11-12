<?php

namespace App\Http\Controllers\Front;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\CompetitionCategory;
use App\Models\CompetitionTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CompetitionDetailController extends Controller
{
    public function detail($id)
    {
        $id = decrypt($id);
        // 1. Get the main competition details
        $competition = DB::table('my_competitions')
            ->where('id', $id)
            ->first();
        $category = CompetitionCategory::findorfail($competition->category_id)->name;
        $competition->category = $category;

        if (!$competition) {
            abort(404, 'Competition not found.');
        }

        // 2. Get prizes associated with this competition
        $prizes = DB::table('competition_prizes')
            ->where('competition_id', $competition->id)
            ->get();

        // 3. Get the active terms and conditions
        $terms = DB::table('competition_terms_versions')
            ->where('id', $competition->terms_id)
            ->where('is_active', 1)
            ->first();


        // 4. Get qualification questions linked to this competition
        $questionIds = $competition->qualification_question_ids ?? [];

        if (is_string($questionIds)) {
            $questionIds = json_decode($questionIds, true) ?? [];
        }

        $qualificationQuestions = [];

        if (is_array($questionIds) && count($questionIds) > 0) {
            $qualificationQuestions = DB::table('qualification_questions')
                ->whereIn('id', $questionIds)
                ->get()
                ->map(function ($question) {
                    // Never send the correct answer to the frontend
                    $question = (array) $question;

                    unset(
                        $question['correct_answer'],
                        $question['correct_option'],
                        $question['correct_option_id']
                    );

                    return $question;
                })
                ->values();
        }

        // 5. Get countries configured for this competition
        $countries = DB::table('competition_country')
            ->where('competition_id', $competition->id)
            ->get();

        $ticketStats = DB::table('competition_tickets')
            ->where('competition_id', $competition->id)
            ->selectRaw('COUNT(*) as total_tickets')
            ->selectRaw("SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as remaining_tickets")
            ->first();

        $totalTickets = $ticketStats->total_tickets;
        $remainingTickets = $ticketStats->remaining_tickets;

        // 6. Pass all detail-page data to Vue
        return Inertia::render('Front/CompetitionDetail', [
            'competition'           => $competition,
            'prizes'                => $prizes,
            'terms'                 => $terms,
            'qualificationQuestions' => $qualificationQuestions,
            'countries'             => $countries,
            'totalTickets' => $totalTickets,
            'remainingTickets' => $remainingTickets
        ]);
    }


    public function checkEligibility(Request $request, $id)
    {
        // 1. Customer must be logged in
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please log in to enter this competition.',
                'redirect' => route('login'),
            ], 401);
        }

        $user = Auth::user();

        // 2. Load competition
        $competition = DB::table('my_competitions')
            ->where('id', $id)
            ->first();

        if (!$competition) {
            return response()->json([
                'success' => false,
                'message' => 'Competition not found.',
            ], 404);
        }

        // 3. Check competition status
        if (!in_array($competition->status, ['published', 'active'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'This competition is not currently accepting entries.',
            ], 422);
        }

        // 4. Check opening date
        Log::info('Competition start check', [
            'starts_at' => $competition->starts_at,
            'parsed_start' => Carbon::parse($competition->starts_at)->toDateTimeString(),
            'now' => Carbon::now()->toDateTimeString(),
            'timezone' => config('app.timezone'),
        ]);
        if (
            $competition->starts_at &&
            Carbon::now()->lt(Carbon::parse($competition->starts_at))
        ) {
            return response()->json([
                'success' => false,
                'message' => 'This competition has not started yet.',
            ], 422);
        }

        // 5. Check closing date
        if (
            $competition->ends_at &&
            Carbon::now()->gte(Carbon::parse($competition->ends_at))
        ) {
            return response()->json([
                'success' => false,
                'message' => 'This competition has ended.',
            ], 422);
        }

        // 6. Check minimum age
        if ($competition->minimum_age) {
            if (!$user->date_of_birth) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please add your date of birth to your profile before entering.',
                    'redirect' => route('profile.edit'),
                ], 422);
            }

            $age = Carbon::parse($user->date_of_birth)->age;

            if ($age < (int) $competition->minimum_age) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not meet the minimum age requirement for this competition.',
                ], 422);
            }
        }

        // 7. Check eligible country
        $userCountry = strtoupper(trim($user->country ?? ''));

        $allowedCountries = DB::table('competition_country')
            ->where('competition_id', $competition->id)
            ->pluck('country_code')
            ->map(fn($country) => strtoupper(trim($country)))
            ->toArray();

        $countryMap = [
            'GUYANA' => 'GY',
        ];

        $userCountryCode = $countryMap[$userCountry] ?? $userCountry;

        if (
            !empty($allowedCountries) &&
            !in_array($userCountryCode, $allowedCountries, true)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Your country is not eligible for this competition.',
            ], 403);
        }

        // 8. Check remaining entry availability
        // Assumes total_entries represents the competition's entry capacity.
        if (
            isset($competition->total_entries) &&
            (int) $competition->total_entries <= 0
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Entries are not available for this competition.',
            ], 422);
        }

        // 9. Load qualification questions without exposing correct answers
        $qualificationQuestions = [];

        if ($competition->qualification_enabled) {
            $questionIds = $competition->qualification_question_ids ?? [];

            if (is_string($questionIds)) {
                $questionIds = json_decode($questionIds, true) ?? [];
            }

            if (is_array($questionIds) && !empty($questionIds)) {
                $qualificationQuestions = DB::table('qualification_questions')
                    ->whereIn('id', $questionIds)
                    ->where('is_active', 1)
                    ->get([
                        'id',
                        'question',
                        'option_a',
                        'option_b',
                        'option_c',
                        'option_d',
                    ]);

                // Ensure every configured question is valid and active.
                $expectedQuestionCount = count(array_unique($questionIds));

                if ($qualificationQuestions->count() !== $expectedQuestionCount) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Some selected qualification questions are missing or inactive. Please check the competition configuration.',
                    ], 422);
                }
            }

            if ($qualificationQuestions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This competition requires qualification questions, but none are configured.',
                ], 422);
            }
        }

        // 10. Return eligibility result and safe question data
        return response()->json([
            'success' => true,
            'requires_qualification' => (bool) $competition->qualification_enabled,
            'competition_id' => $competition->id,
            'qualification_questions' => $qualificationQuestions,
            'message' => $competition->qualification_enabled
                ? 'Eligibility verified. Please answer the qualification questions.'
                : 'Eligibility verified. You can continue to the cart.',
        ]);
    }

    public function submitQualification(Request $request, $id)
    {
        $validated = $request->validate([
            'answers' => ['required', 'array', 'min:1'],
            'answers.*' => ['required', 'in:a,b,c,d'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $competition = DB::table('my_competitions')
            ->where('id', $id)
            ->first();

        if (!$competition) {
            return response()->json([
                'success' => false,
                'message' => 'Competition not found.',
            ], 404);
        }

        if (!$competition->qualification_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'Qualification is not enabled for this competition.',
            ], 422);
        }

        $questionIds = $competition->qualification_question_ids ?? [];

        if (is_string($questionIds)) {
            $questionIds = json_decode($questionIds, true) ?? [];
        }

        $questionIds = array_values(array_unique(array_map(
            'intval',
            is_array($questionIds) ? $questionIds : []
        )));

        if (empty($questionIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No qualification questions are configured.',
            ], 422);
        }

        $answers = array_map(
            fn($answer) => strtolower(trim($answer)),
            $validated['answers']
        );
        Log::info('Qualification answer debugging', [
            'competition_id' => $competition->id,
            'configured_question_ids' => $questionIds,
            'submitted_answers' => $answers,
        ]);

        // Require an answer for every configured question.
        $submittedIds = array_map('intval', array_keys($answers));
        sort($submittedIds);


        $expectedIds = $questionIds;
        sort($expectedIds);

        if ($submittedIds !== $expectedIds) {
            return response()->json([
                'success' => false,
                'message' => 'Please answer every qualification question.',
            ], 422);
        }

        $questions = DB::table('qualification_questions')
            ->whereIn('id', $questionIds)
            ->where('is_active', 1)
            ->get(['id', 'correct_answer']);

        if ($questions->count() !== count($questionIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some qualification questions are unavailable.',
            ], 422);
        }

        foreach ($questions as $question) {
            $submittedAnswer = strtolower(trim(
                (string) ($answers[$question->id] ?? '')
            ));

            $correctAnswer = strtolower(trim(
                (string) ($question->correct_answer ?? '')
            ));

            // Temporary debugging
            Log::info('Checking qualification answer', [
                'question_id' => $question->id,
                'submitted_answer' => $submittedAnswer,
                'correct_answer' => $correctAnswer,
            ]);

            if (
                $submittedAnswer === '' ||
                $correctAnswer === '' ||
                $submittedAnswer !== $correctAnswer
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'You did not answer the questions correctly.',
                ], 422);
            }
        }

        // All answers are correct. Eligibility must be checked again
        // before allowing the competition to be added to the cart.
        return response()->json([
            'success' => true,
            'message' => 'All answers are correct.',
            'competition_id' => (int) $competition->id,
            'quantity' => $validated['quantity'],
        ]);
    }
}
