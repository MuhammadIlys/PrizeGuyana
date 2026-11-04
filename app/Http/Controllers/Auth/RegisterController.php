<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password; // <--- Import Password rule here
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    public function index()
    {
        return Inertia('Auth/Register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // dd($request);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'country' => $validated['country'],
            'date_of_birth' => $validated['date_of_birth'],
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        event(new \Illuminate\Auth\Events\Registered($user));

        Auth::login($user);

        return redirect(route('user.dashboard', absolute: false));
    }
}
