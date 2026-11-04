<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NewsLetterSubscribeController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('home')->with('success', 'Subscribed successfully!');
    }
}
