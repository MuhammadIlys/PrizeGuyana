<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsLetterSubscribeController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));

        NewsletterSubscriber::firstOrCreate([
            'email' => $email,
        ]);

        return redirect()
            ->route('home')
            ->with('success', 'Thank you! You have successfully subscribed to our newsletter.');
    }
}
