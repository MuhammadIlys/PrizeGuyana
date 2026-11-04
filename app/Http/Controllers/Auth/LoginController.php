<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return Auth::user()->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('user.dashboard');
        }

        return Inertia::render('Auth/Login');
    }
    public function login(LoginRequest $request)
    {
        // Validates input, checks rate limits, and authenticates
        $request->authenticate();

        // Regenerate session to prevent session fixation attacks
        $request->session()->regenerate();

        $targetRoute = $request->user()->isAdmin() ? 'admin.dashboard' : 'user.dashboard';

        return redirect()->intended(route($targetRoute));
    }
}
