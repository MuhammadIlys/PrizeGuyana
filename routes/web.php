<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Front\AboutController;
use App\Http\Controllers\Front\Auth\ForgotPasswordController;
use App\Http\Controllers\Front\CartController;
use App\Http\Controllers\Front\CheckoutController;
use App\Http\Controllers\Front\CompetitionDetailController;
use App\Http\Controllers\Front\ContactController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\NewsLetterSubscribeController;
use App\Http\Controllers\Front\PrivacyPolicyController;
use App\Http\Controllers\Front\StripeWebhookController;
use App\Http\Controllers\Front\TermisandconditionsController;
use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| Public Frontend Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [AboutController::class, 'index'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::get('/termsandconditions', [TermisandconditionsController::class, 'index'])->name('termsandconditions');
Route::get('/privacypolicy', [PrivacyPolicyController::class, 'index'])->name('privacypolicy');
Route::post('/newsletter-subscribe', [NewsLetterSubscribeController::class, 'index'])->name('newsletter.subscribe');


/*
|--------------------------------------------------------------------------
| Cart Routes
|--------------------------------------------------------------------------
*/
Route::prefix('cart')->name('cart.')->controller(CartController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/add', 'add')->name('add');
    Route::patch('/{competitionId}', 'update')->name('update');
    Route::delete('/{competitionId}', 'remove')->name('remove');
});

/*
|--------------------------------------------------------------------------
| Competition Details
|--------------------------------------------------------------------------
*/
Route::get('/competition-detail/{id}', [CompetitionDetailController::class, 'detail'])->name('competition.detail');

Route::post(
    '/competition-detail/{id}/qualification',
    [CompetitionDetailController::class, 'submitQualification']
)->middleware('auth')->name('competition.qualification');

Route::post('/competition-detail/{id}/eligibility', [CompetitionDetailController::class, 'checkEligibility',])->name('competition.eligibility');


/*
|--------------------------------------------------------------------------
| Checkout
|--------------------------------------------------------------------------
*/
Route::prefix('checkout')->name('checkout.')->controller(CheckoutController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::post('/', 'store')->name('store');
});

Route::post('/stripe/webhook', [
    StripeWebhookController::class,
    'handle',
])->name('stripe.webhook');


/*
|--------------------------------------------------------------------------
| Guest Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Registration
    Route::get('/register', [RegisterController::class, 'index'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('user.register');

    // Email Verification
    Route::controller(EmailVerificationController::class)->group(function () {
        Route::get('/email/verify', 'show')->name('email.verify');
        Route::post('/email/verify', 'verify')->name('email.verify.submit');
        Route::post('/email/verify/resend', 'resend')->name('email.verify.resend');
    });


    // Authentication
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('user.login');

    // Password Reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])->name('forgot.password');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

    // Required by Laravel Mailer for email links
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LogoutController::class, 'index'])->name('logout');
});
