<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerification;
use App\Models\User;
use App\Notifications\VerifyEmailOtp;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class EmailVerificationController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $email = $request->session()->get('pending_email_verification');

        if (!$email || !EmailVerification::where('email', $email)->exists()) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Please register first.']);
        }

        return Inertia::render('Auth/VerifyEmail', [
            'email' => $email,
        ]);
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $email = $request->session()->get('pending_email_verification');

        if (!$email) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Your verification session has expired. Please register again.']);
        }

        $verification = EmailVerification::where('email', $email)->first();

        if (!$verification) {
            $request->session()->forget('pending_email_verification');

            return redirect()->route('register')
                ->withErrors(['email' => 'Verification details not found. Please register again.']);
        }

        if ($verification->expires_at->isPast()) {
            $verification->delete();
            $request->session()->forget('pending_email_verification');

            return redirect()->route('register')
                ->withErrors(['email' => 'Your verification code has expired. Please register again.']);
        }

        if ($verification->attempts >= 5) {
            return back()->withErrors([
                'otp' => 'Too many incorrect attempts. Please request a new code.',
            ]);
        }

        if (!Hash::check($request->otp, $verification->otp_hash)) {
            $verification->increment('attempts');

            throw ValidationException::withMessages([
                'otp' => 'The verification code is incorrect.',
            ]);
        }

        // Create the account only after successful email verification.
        $user = DB::transaction(function () use ($email, $request) {
            $verification = EmailVerification::where('email', $email)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $verification->expires_at->isPast() ||
                $verification->attempts >= 5 ||
                !Hash::check($request->otp, $verification->otp_hash)
            ) {
                throw ValidationException::withMessages([
                    'otp' => 'The verification code is invalid or expired.',
                ]);
            }

            if (User::where('email', $email)->exists()) {
                $verification->delete();

                throw ValidationException::withMessages([
                    'email' => 'This email address is already registered. Please log in.',
                ]);
            }

            $user = User::create([
                'first_name' => $verification->first_name,
                'last_name' => $verification->last_name,
                'email' => $verification->email,
                'password' => $verification->password,
                'country' => $verification->country,
                'date_of_birth' => $verification->date_of_birth,
                'phone_number' => $verification->phone_number,
            ]);

            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();

            $verification->delete();

            return $user;
        });

        $request->session()->forget('pending_email_verification');

        event(new \Illuminate\Auth\Events\Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        $notificationService = app(NotificationService::class);

        // Notify the newly registered customer
        $notificationService->send(
            $user,
            'Welcome to PrizeGuyana!',
            'Your account has been successfully created and your email is verified.',
            route('user.dashboard'),
            'registration'
        );

        // Notify administrators
        $notificationService->notifyAdmins(
            'New Customer Registered',
            $user->first_name . ' ' . $user->last_name . ' has successfully registered.',
            route('admin.customers.index'),
            'registration'
        );

        return redirect()->route('user.dashboard');
    }

    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get('pending_email_verification');

        if (!$email) {
            return redirect()->route('register')
                ->withErrors(['email' => 'Please register again to request a verification code.']);
        }

        $verification = EmailVerification::where('email', $email)->first();

        if (!$verification) {
            $request->session()->forget('pending_email_verification');

            return redirect()->route('register')
                ->withErrors(['email' => 'Registration details not found. Please register again.']);
        }

        if (
            $verification->last_sent_at &&
            $verification->last_sent_at->gt(now()->subSeconds(60))
        ) {
            return back()->withErrors([
                'otp' => 'Please wait before requesting another code.',
            ]);
        }

        $otp = (string) random_int(100000, 999999);

        $verification->update([
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
            'attempts' => 0,
        ]);

        Notification::route('mail', $email)
            ->notify(new VerifyEmailOtp($otp));

        return back()->with('success', 'A new verification code has been sent.');
    }
}
