<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\RegisterRequest;
use App\Models\EmailVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Notifications\VerifyEmailOtp;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function index()
    {
        return Inertia::render('Auth/Register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Check whether an OTP was recently sent
        $existing = EmailVerification::where(
            'email',
            $validated['email']
        )->first();

        if (
            $existing &&
            $existing->last_sent_at &&
            $existing->last_sent_at->gt(now()->subSeconds(60))
        ) {
            return back()->withErrors([
                'email' => 'Please wait 60 seconds before requesting another verification code.',
            ]);
        }

        // Generate a secure 6-digit OTP
        $otp = (string) random_int(100000, 999999);

        // Store registration details temporarily
        $verification = EmailVerification::updateOrCreate(
            ['email' => $validated['email']],
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'password' => $validated['password'],
                'country' => $validated['country'],
                'date_of_birth' => $validated['date_of_birth'],
                'phone_number' => $validated['phone_number'] ?? null,
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(5),
                'last_sent_at' => now(),
                'attempts' => 0,
            ]
        );

        // Remember the pending email in the session
        $request->session()->put(
            'pending_email_verification',
            $verification->email
        );

        // Send OTP email
        Notification::route('mail', $verification->email)
            ->notify(new VerifyEmailOtp($otp));

        return redirect()->route('email.verify')
            ->with('success', 'A verification code has been sent to your email address.');
    }
}
