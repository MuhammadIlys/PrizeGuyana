<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompetitionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => [
                'required',
                'integer',
                'exists:competition_categories,id',
            ],
            'terms_id' => [
                'required',
                'integer',
                'exists:competition_terms_versions,id',
            ],

            'short_description' => [
                'required',
                'string',
                'max:500',
            ],
            'description' => ['required', 'string'],

            'max_entries_per_customer' => [
                'required',
                'integer',
                'min:1',
            ],

            // Keep the existing image if no replacement is uploaded.
            'main_image' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],

            // Keep the existing video if no replacement is uploaded.
            'video' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:mp4,mov,webm',
                'max:51200',
            ],

            'entry_price' => ['required', 'numeric', 'min:0'],
            'total_entries' => ['required', 'integer', 'min:1'],

            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'draw_at' => [
                'required',
                'date',
                'after_or_equal:ends_at',
            ],

            'minimum_age' => ['required', 'integer', 'min:18'],
            'qualification_question_ids' => ['nullable', 'array'],
            'qualification_question_ids.*' => [
                'integer',
                'distinct',
                'exists:qualification_questions,id',
            ],

            'country_codes' => ['nullable', 'array'],
            'country_codes.*' => [
                'string',
                'distinct',
                'size:2',
            ],

            'is_featured' => ['sometimes', 'boolean'],
            'qualification_enabled' => ['sometimes', 'boolean'],
            'instant_win_enabled' => ['sometimes', 'boolean'],
            'status' => ['sometimes'],
            'published_at' => ['sometimes'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please enter a competition title.',

            'category_id.required' => 'Please select a competition category.',
            'category_id.exists' => 'The selected category is invalid.',

            'terms_id.required' => 'Please select competition terms.',
            'terms_id.exists' => 'The selected terms are invalid.',

            'short_description.required' => 'Please enter a short description.',
            'description.required' => 'Please enter the full description.',

            'max_entries_per_customer.required' =>
            'Please enter the maximum entries per customer.',
            'max_entries_per_customer.min' =>
            'The maximum entries per customer must be at least 1.',

            'main_image.image' => 'The main image must be a valid image.',
            'main_image.mimes' => 'The image must be JPG, JPEG, PNG, or WebP.',
            'main_image.max' => 'The image size must not exceed 5 MB.',

            'video.mimes' => 'The video must be MP4, MOV, or WebM.',
            'video.max' => 'The video size must not exceed 50 MB.',

            'entry_price.required' => 'Please enter the entry price.',
            'entry_price.min' => 'The entry price cannot be negative.',

            'total_entries.required' => 'Please enter the total number of entries.',
            'total_entries.min' => 'The competition must have at least one entry.',

            'starts_at.required' => 'Please select the competition start date.',
            'ends_at.required' => 'Please select the competition closing date.',
            'ends_at.after' => 'The closing date must be after the start date.',

            'draw_at.required' => 'Please select the draw date.',
            'draw_at.after_or_equal' =>
            'The draw date must be on or after the closing date.',

            'minimum_age.required' => 'Please provide the minimum age.',
            'minimum_age.min' => 'The minimum age must be at least 18.',

        ];
    }
}
