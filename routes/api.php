<?php
use App\Http\Controllers\Front\StripeWebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/stripe/webhook', [
    StripeWebhookController::class,
    'handle',
])->name('stripe.webhook');