<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CompetitionQualification\StoreCompetitionQualificationQuestionRequest;
use App\Http\Requests\Admin\CompetitionQualification\UpdateCompetitionQualificationQuestionRequest;
use App\Http\Requests\StoreQualificationQuestionRequest;
use App\Http\Requests\UpdateQualificationQuestionRequest;
use App\Models\QualificationQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CompetitionQualificationQuestionController extends Controller
{
    public function index()
    {
        $questions = QualificationQuestion::query()
            ->latest()
            ->paginate(5)
            ->withQueryString()
            ->through(function ($question) {
                $question->encrypted_id = Crypt::encryptString(
                    (string) $question->id
                );

                return $question;
            });

        return Inertia::render('Admin/QualificationQuestions/Index', [
            'questions' => $questions,
        ]);
    }

    public function create()
    {
        return inertia('Admin/QualificationQuestions/Create');
    }

    public function store(StoreQualificationQuestionRequest $request)
    {

        QualificationQuestion::create($request->validated());

        return redirect()
            ->route('admin.qualification-questions.index')
            ->with('success', 'Qualification question created successfully.');
    }


    public function edit($id)
    {
        $id = Crypt::decryptString($id);
        $qualificationQuestion = QualificationQuestion::findorfail($id);
        return Inertia::render('Admin/QualificationQuestions/Edit', [
            'question' => $qualificationQuestion,
        ]);
    }

    public function update(
        UpdateQualificationQuestionRequest $request,
        QualificationQuestion $qualificationQuestion
    ) {
        $qualificationQuestion->update($request->validated());

        return redirect()
            ->route('admin.qualification-questions.index')
            ->with('success', 'Qualification question updated successfully.');
    }

    public function destroy($id, Request $request)
    {
        $id = Crypt::decryptString($id);
        $question = QualificationQuestion::findorfail($id);
        if ($question->delete()) {
            return redirect()->route('admin.qualification-questions.index')->with('success', 'Question deleted successfully');
        } else {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }
}
