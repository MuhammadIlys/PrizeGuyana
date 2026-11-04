<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CompetitionCategoryController;
use App\Http\Controllers\Admin\CompetitionPrizeController;
use App\Http\Controllers\Admin\CompetitionQualificationQuestionController;
use App\Http\Controllers\Admin\CompetitionStatusController;
use App\Http\Controllers\Admin\CompetitionTermsVersionController;
use App\Http\Controllers\Admin\CompetitionTicketController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\EntriesController;
use App\Http\Controllers\Admin\MyCompetitionController;
use App\Http\Controllers\Admin\OrdersController;
use Illuminate\Support\Facades\Route;




Route::middleware(['auth', 'admin'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Competitions
    Route::resource('competitions', MyCompetitionController::class);

    // Competition Categories
    Route::resource('competition-categories', CompetitionCategoryController::class);

    // Competition Terms
    Route::resource('competition-terms', CompetitionTermsVersionController::class);

    // Qualification Questions
    Route::resource('qualification-questions', CompetitionQualificationQuestionController::class);

    //Competition Prize Management
    Route::resource('competition-prizes', CompetitionPrizeController::class);

    Route::controller(CompetitionTicketController::class)->prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{competitionId}', 'show')->name('show');
        Route::post('/{competitionId}/generate', 'generate')->name('generate');
        
        Route::post('/{competitionId}/preview-allocation', 'previewAllocation')
            ->name('preview-allocation');
        Route::post('/{competitionId}/allocate-winning-tickets', 'allocateWinningTickets')
            ->name('allocate-winning-tickets');
    });

    Route::post(
        '/competitions/{competition}/toggle-status',
        [CompetitionStatusController::class, 'toggle']
    )->name('competitions.toggle-status');

    Route::resource('orders', OrdersController::class)
        ->only(['index', 'show']);

    Route::resource('customers', CustomerController::class)
        ->only(['index', 'show']);

    Route::resource('entries', EntriesController::class)
        ->only(['index', 'show']);
});
