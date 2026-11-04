<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TermisandconditionsController extends Controller
{
    public function index()
    {
        return Inertia::render('Front/Terms', [
            'lastUpdated' => 'September 2026',
            'supportEmail' => 'support@prizeguyana.com',
            'sections' => [
                [
                    'id' => 'overview',
                    'title' => '1. Overview & Acceptance of Terms',
                    'content' => [
                        'Welcome to Prize Guyana. By accessing our website, creating an account, or entering any of our competitions, you agree to be bound by these Terms and Conditions. If you do not agree with any part of these terms, you must refrain from participating in our competitions.',
                        'Prize Guyana operates skill-based promotional competitions compliant with applicable gaming and raffle regulations. All competitions are operated independently and are in no way sponsored, endorsed, or administered by Apple Inc., Google LLC, or vehicle/brand manufacturers featured as prizes.',
                    ],
                ],
                [
                    'id' => 'eligibility',
                    'title' => '2. Eligibility & User Verification',
                    'content' => [
                        'Participation in Prize Guyana competitions is subject to strict eligibility requirements to maintain full regulatory compliance:',
                    ],
                    'bullets' => [
                        'Participants must be at least 18 years of age (or the legal age of majority in their jurisdiction).',
                        'Participants must hold a valid government-issued ID upon request for winner verification.',
                        'Employees, direct family members, and affiliates of Prize Guyana are strictly prohibited from participating in any draws.',
                    ],
                    'note' => 'Prize Guyana reserves the right to request proof of age and identity at any time. Accounts created using fraudulent details will be permanently banned and tickets forfeited.',
                ],
                [
                    'id' => 'tickets',
                    'title' => '3. Ticket Purchases & Allocation',
                    'content' => [
                        'Each competition lists a set ticket price and a maximum ticket cap per competition and per user. Ticket numbers are randomly or manually allocated upon successful payment processing through our authorized payment processing gateways.',
                        'All ticket purchases are final once confirmed. Successfully allocated ticket numbers will be immediately visible inside your account dashboard under "My Tickets".',
                    ],
                ],
                [
                    'id' => 'draws',
                    'title' => '4. Competition Draws & Winner Selection',
                    'content' => [
                        'Draws take place on the published date, or as soon as all available tickets are sold out. Winners are selected via an automated, verifiable Random Number Generator (RNG) or broadcasted via live draw events on our official social channels.',
                        'Winners will be contacted directly within 48 hours of the draw via registered phone and email. If a winner cannot be reached within 14 business days, Prize Guyana reserves the right to re-draw an alternate winner.',
                    ],
                ],
                [
                    'id' => 'prizes',
                    'title' => '5. Prizes, Delivery & Cash Alternatives',
                    'content' => [
                        'Prizes are as described in each competition listing. In certain competitions, a cash alternative may be offered at Prize Guyana\'s discretion.',
                        'Physical prize delivery logistics (e.g., vehicles, luxury items) will be coordinated directly with the verified winner. Local delivery or collection arrangements are finalized upon identity verification.',
                    ],
                ],
                [
                    'id' => 'refunds',
                    'title' => '6. Cancellation & Refunds',
                    'content' => [
                        'Due to the nature of live ticket allocation and fixed draw schedules, ticket purchases are non-refundable once a transaction is completed, except in the event that a competition is cancelled by Prize Guyana without a winner selection.',
                    ],
                ],
                [
                    'id' => 'conduct',
                    'title' => '7. Acceptable Use & Prohibitions',
                    'content' => [
                        'Users agree not to engage in automated bot entries, payment chargeback fraud, multi-account abuse, or security exploitation. Any violation will result in immediate termination of the account and forfeiture of all entries.',
                    ],
                ],
                [
                    'id' => 'limitation',
                    'title' => '8. Limitation of Liability',
                    'content' => [
                        'Prize Guyana accepts no liability for technical interruptions, server outages, or payment gateway failures beyond our reasonable control. We aim to ensure 99.9% platform availability.',
                    ],
                ],
                [
                    'id' => 'contact',
                    'title' => '9. Contact & Support',
                    'content' => [
                        'If you have questions regarding these Terms & Conditions, please contact our support team.',
                    ],
                ],
            ],
        ]);
    }
}
