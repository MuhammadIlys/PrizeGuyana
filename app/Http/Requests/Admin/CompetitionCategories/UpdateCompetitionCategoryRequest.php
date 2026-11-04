<?php

namespace App\Http\Requests\Admin\CompetitionCategories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompetitionCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('competition_category');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(
                    'competition_categories',
                    'name'
                )->ignore($category->id),
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(
                    'competition_categories',
                    'slug'
                )->ignore($category->id),
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