
<?php

use App\Http\Controllers\User\Dashboard;
use App\Http\Controllers\User\EntriesController;
use App\Http\Controllers\User\OrderController;
use App\Http\Controllers\User\PasswordController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\TicketsController;
use App\Http\Controllers\User\TransactionController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [Dashboard::class, 'index'])
        ->name('dashboard');

    Route::prefix('orders')
        ->name('orders.')
        ->controller(OrderController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/history', 'history')->name('history');
            Route::get('/{order}', 'orderDetails')->name('show');
        });

    Route::prefix('transactions')
        ->name('transactions.')
        ->controller(TransactionController::class)
        ->group(function () {
            Route::get('/', 'transactions')->name('index');
            Route::get('/{payment}', 'transactionDetails')->name('show');
        });

    Route::prefix('tickets')
        ->name('tickets.')
        ->controller(TicketsController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
        });

    Route::get('/my-prizes/{winningTicket}', [
        TicketsController::class,
        'showPrize'
    ])->name('prizes.show');

    Route::post(
        '/my-prizes/{winningTicket}/claim',
        [TicketsController::class, 'submitPrizeClaim']
    )->name('prizes.claim');


    Route::prefix('entries')
        ->name('entries.')
        ->controller(EntriesController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
        });
    Route::prefix('profile')->name('profile.')->controller(ProfileController::class)->group(function () {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
    });

    Route::prefix('password')
        ->name('password.')
        ->controller(PasswordController::class)
        ->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::put('/', 'update')->name('update');
        });
});
