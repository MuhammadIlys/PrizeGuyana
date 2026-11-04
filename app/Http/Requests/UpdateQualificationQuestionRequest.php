<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQualificationQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $questionId = $this->route('qualification_question');

        // Supports either a route-bound model or a route ID.
        if (is_object($questionId)) {
            $questionId = $questionId->getKey();
        }

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('qualification_questions', 'slug')
                    ->ignore($questionId),
            ],
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string', 'max:255'],
            'option_b' => ['required', 'string', 'max:255'],
            'option_c' => ['required', 'string', 'max:255'],
            'option_d' => ['required', 'string', 'max:255'],
            'correct_answer' => [
                'required',
                Rule::in(['a', 'b', 'c', 'd']),
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
