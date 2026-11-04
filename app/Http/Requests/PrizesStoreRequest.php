<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PrizesStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'competition_id' => ['required', 'integer', 'exists:my_competitions,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'prize_role' => ['required', 'in:main,small'],
            'prize_type' => ['required', 'in:physical,cash'],
            'image' => ['nullable', 'image', 'max:5120'],
            'prize_value' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'currency' => ['required', 'string', 'size:3'],
            'quantity' => ['required', 'integer', 'min:1'],
            'winning_method' => ['required', 'in:draw,instant_win'],
            'status' => ['required', 'in:active,inactive'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }


    public function messages(): array
    {
        return [
            'competition_id.required' => 'Please select a competition.',
            'competition_id.integer' => 'The selected competition is invalid.',
            'competition_id.exists' => 'The selected competition does not exist.',

            'name.required' => 'Please enter the prize name.',
            'name.string' => 'The prize name must be valid text.',
            'name.max' => 'The prize name cannot exceed 255 characters.',

            'description.string' => 'The prize description must be valid text.',

            'prize_role.required' => 'Please select a prize role.',
            'prize_role.in' => 'The selected prize role is invalid.',

            'prize_type.required' => 'Please select a prize type.',
            'prize_type.in' => 'The selected prize type is invalid.',

            'image.image' => 'The prize image must be a valid image file.',
            'image.max' => 'The prize image cannot exceed 5 MB.',

            'prize_value.numeric' => 'The prize value must be a valid number.',
            'prize_value.min' => 'The prize value cannot be negative.',
            'prize_value.max' => 'The prize value is too large.',

            'currency.required' => 'Please select a currency.',
            'currency.string' => 'The currency must be valid text.',
            'currency.size' => 'The currency code must contain exactly 3 characters.',

            'quantity.required' => 'Please enter the prize quantity.',
            'quantity.integer' => 'The prize quantity must be a whole number.',
            'quantity.min' => 'The prize quantity must be at least 1.',

            'winning_method.required' => 'Please select a winning method.',
            'winning_method.in' => 'The selected winning method is invalid.',

            'status.required' => 'Please select a prize status.',
            'status.in' => 'The selected prize status is invalid.',

            'sort_order.integer' => 'The display order must be a whole number.',
            'sort_order.min' => 'The display order cannot be negative.',
        ];
    }
}
