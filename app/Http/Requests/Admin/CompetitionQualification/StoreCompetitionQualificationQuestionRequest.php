<?php

namespace App\Http\Requests\Admin\CompetitionQualification;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompetitionQualificationQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question' => [
                'required',
                'string',
            ],

            'question_type' => [
                'required',
                'in:text,multiple_choice',
            ],

            'is_required' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'options' => [
                'nullable',
                'array',
            ],

            'options.*.option_text' => [
                'required',
                'string',
                'max:255',
            ],

            'options.*.is_correct' => [
                'required',
                'boolean',
            ],

            'options.*.sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' =>
                'Please enter the qualification question.',

            'question.string' =>
                'The qualification question must be valid text.',

            'question_type.required' =>
                'Please select the question type.',

            'question_type.in' =>
                'The selected question type is invalid.',

            'is_required.boolean' =>
                'The required option must be true or false.',

            'sort_order.integer' =>
                'The sort order must be a whole number.',

            'sort_order.min' =>
                'The sort order cannot be negative.',

            'is_active.boolean' =>
                'The active option must be true or false.',

            'options.array' =>
                'The qualification options must be valid.',

            'options.*.option_text.required' =>
                'Each qualification option must have text.',

            'options.*.option_text.string' =>
                'Each qualification option must be valid text.',

            'options.*.option_text.max' =>
                'Each qualification option cannot exceed 255 characters.',

            'options.*.is_correct.required' =>
                'Please specify whether each option is correct.',

            'options.*.is_correct.boolean' =>
                'The correct option value must be true or false.',

            'options.*.sort_order.integer' =>
                'The option sort order must be a whole number.',

            'options.*.sort_order.min' =>
                'The option sort order cannot be negative.',
        ];
    }
}