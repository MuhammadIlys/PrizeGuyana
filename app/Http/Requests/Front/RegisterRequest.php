<?php

namespace App\Http\Requests\Front;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name'    => ['required', 'string', 'max:255'],
            'last_name'     => ['required', 'string', 'max:255'],
            'email'         => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password'      => ['required', 'confirmed', Password::defaults()],
            'country'       => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date', 'before:-18 years'],
            'phone_number'  => ['nullable', 'string', 'max:30'],
            'terms'         => ['accepted'],
            'privacy'       => ['accepted'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required'   => 'Please enter your first name.',
            'last_name.required'    => 'Please enter your last name.',
            'email.required'        => 'An email address is required.',
            'email.email'           => 'Please enter a valid email address.',
            'email.unique'          => 'This email address is already registered.',
            'password.required'     => 'A password is required.',
            'password.confirmed'    => 'Password confirmation does not match.',
            'country.required'      => 'Please select your country of residence.',
            'date_of_birth.required' => 'Date of birth is required.',
            'date_of_birth.before'  => 'You must be at least 18 years old to register.',
            'terms.accepted'        => 'You must agree to the Terms & Conditions.',
            'privacy.accepted'      => 'You must agree to the Privacy Policy.',
        ];
    }
}
