<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('User/Profile/Edit', [
            'profile' => [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'name' => $user->name,
                'email' => $user->email,
                'country' => $user->country,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'phone_number' => $user->phone_number,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'date_of_birth' => [
                'nullable',
                'date',
                'before:today',
            ],
            'phone_number' => ['nullable', 'string', 'max:30'],
        ]);

        $validated['name'] = trim(
            $validated['first_name'] . ' ' . $validated['last_name']
        );

        $user->fill($validated);
        $user->save();

        return redirect()
            ->route('user.profile.edit')
            ->with('success', 'Your profile has been updated successfully.');
    }
}
