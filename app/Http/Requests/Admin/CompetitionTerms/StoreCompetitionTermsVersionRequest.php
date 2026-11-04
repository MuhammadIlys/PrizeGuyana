<?php

namespace App\Http\Requests\Admin\CompetitionTerms;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompetitionTermsVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'version' => [
                'required',
                'string',
                'max:50',
                'unique:competition_terms_versions,version',
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