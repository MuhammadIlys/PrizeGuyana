<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('User/Password/Edit');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()  // At least one uppercase and one lowercase letter
                    ->numbers()    // At least one number
                    ->symbols()    // At least one special character
                    ->uncompromised(), // Reject commonly compromised passwords
                'different:current_password',
            ],
        ]);

        $user = $request->user();

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()
            ->route('user.password.edit')
            ->with('success', 'Your password has been updated successfully.');
    }
}
