<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PrivacyPolicyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Front/Privacy', [
            'lastUpdated' => 'September 2026',
            'supportEmail' => 'privacy@prizeguyana.com',
            'sections' => [
                [
                    'id' => 'collection',
                    'title' => '1. Information We Collect',
                    'content' => [
                        'To provide a secure and transparent experience on Prize Guyana, we collect personal information when you register an account, enter competitions, or interact with our services.',
                    ],
                    'bullets' => [
                        'Identity Data: Full name, date of birth, and identity verification documents for prize claims.',
                        'Contact Information: Email address, phone number, and physical mailing address.',
                        'Account & Transaction Data: Purchase history, ticket numbers, entries, and account preferences.',
                        'Technical & Usage Data: IP address, browser type, device details, and site navigation metrics.',
                    ],
                ],
                [
                    'id' => 'usage',
                    'title' => '2. How We Use Your Information',
                    'content' => [
                        'Your data is processed strictly for essential platform functionality, compliance, and user safety:',
                    ],
                    'bullets' => [
                        'To process ticket purchases and verify competition entries.',
                        'To notify competition winners and manage prize delivery logistics.',
                        'To conduct age verification and fraud prevention checks.',
                        'To send important account updates and optional promotional communications (which you can opt out of at any time).',
                    ],
                ],
                [
                    'id' => 'sharing',
                    'title' => '3. Data Sharing & Third Parties',
                    'content' => [
                        'Prize Guyana does not sell or rent your personal information to third parties. We share data only with trusted service providers necessary to operate our platform:',
                    ],
                    'bullets' => [
                        'Payment Gateways: To securely process ticket transactions.',
                        'Verification Services: For automated or manual identity and age verification.',
                        'Courier & Delivery Partners: To coordinate shipping for physical prizes to verified winners.',
                        'Regulatory & Legal Authorities: When required by law, court order, or gaming regulatory mandates.',
                    ],
                ],
                [
                    'id' => 'security',
                    'title' => '4. Data Security & Retention',
                    'content' => [
                        'We enforce enterprise-grade security controls—including SSL encryption, access restrictions, and secure database storage—to protect your personal details against unauthorized access, loss, or disclosure.',
                        'We retain your personal data only as long as required to fulfill legal, accounting, and regulatory compliance obligations associated with promotional skill competitions.',
                    ],
                ],
                [
                    'id' => 'rights',
                    'title' => '5. Your Rights & Choices',
                    'content' => [
                        'You have full control over your personal data under applicable privacy laws:',
                    ],
                    'bullets' => [
                        'Access & Rectification: Request a copy of your personal data or ask us to update inaccurate records.',
                        'Deletion: Request deletion of your account and data, subject to legal retention obligations.',
                        'Communication Preferences: Manage marketing email subscriptions directly from your account settings.',
                    ],
                ],
                [
                    'id' => 'cookies',
                    'title' => '6. Cookies & Tracking Technologies',
                    'content' => [
                        'We use essential cookies to maintain session states, save user preferences, and analyze site performance. You can manage cookie preferences via your browser settings.',
                    ],
                ],
                [
                    'id' => 'contact',
                    'title' => '7. Privacy Contact & Inquiries',
                    'content' => [
                        'If you have questions, concerns, or requests regarding this Privacy Policy, please reach out to our dedicated privacy officer.',
                    ],
                ],
            ],
        ]);
    }
}
