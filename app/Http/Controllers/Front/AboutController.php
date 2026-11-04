<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AboutController extends Controller
{
    public function index()
    {
        $stats = [
            [
                'value' => '4.8',
                'label' => 'on Trustpilot',
            ],
            [
                'value' => '£250M+',
                'label' => 'in prizes',
            ],
            [
                'value' => '£1m+',
                'label' => 'in charity donations',
            ],
        ];

        return Inertia::render('Front/About', [
            'title' => 'About Us',
            'stats' => $stats,
        ]);
    }
}
