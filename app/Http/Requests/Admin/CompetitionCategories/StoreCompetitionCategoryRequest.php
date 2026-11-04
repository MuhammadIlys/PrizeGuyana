<?php

namespace App\Http\Requests\Admin\CompetitionCategories;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompetitionCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:competition_categories,name',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:competition_categories,slug',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }
}