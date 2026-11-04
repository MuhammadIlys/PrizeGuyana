<?php

namespace App\Http\Requests\Admin\CompetitionTerms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompetitionTermsVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $competitionTerm = $this->route('competition_term');

        return [
            'version' => [
                'required',
                'string',
                'max:50',
                Rule::unique(
                    'competition_terms_versions',
                    'version'
                )->ignore($competitionTerm->id),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'content' => [
                'required',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}