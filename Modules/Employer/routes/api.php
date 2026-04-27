<?php

use Illuminate\Support\Facades\Route;
use Modules\Employer\Http\Controllers\EmployerController;

Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function () {
    Route::apiResource('employers', EmployerController::class)->names('employer');
    Route::post('employers-comment', [EmployerController::class, 'EmployerComment'])->name('employer-EmployerComment');
    Route::post('employers-subscription', [EmployerController::class, 'EmployerSubscription'])->name('EmployerSubscription');
    Route::post('/employers-cost', [EmployerController::class, 'EmployerCostStore'])->name('EmployerCostStore');
    Route::get('/employer-deposit', [EmployerController::class, 'EmployerDepositIndex'])->name('EmployerDepositIndex');
    Route::put('/employer-deposit/{id}', [EmployerController::class, 'EmployerUpdateDeposit'])->name('EmployerUpdateDeposit');

    Route::get('/employer-cost/{employerId}', [EmployerController::class, 'EmployerCost'])->name("admin-EmployerCost");
});
Route::middleware(['auth:sanctum'])->prefix('v1/employer')->group(function () {
    Route::get('/info', [EmployerController::class, 'info'])->name("employer-info");
    Route::get('/tickets/{id}', [EmployerController::class, 'ticketDetail'])->name("employer-ticketDetail");
    
    Route::post('/messages', [EmployerController::class, 'messageStore'])->name("employer-messageStore");
    Route::get('/dashboard', [EmployerController::class, 'dashboard'])->name("employer-dashboard");
    Route::get('/deposit', [EmployerController::class, 'deposit'])->name("employer-deposit");
    Route::post('/receipt', [EmployerController::class, 'receipt'])->name("employer-receipt");
    Route::get('/cost', [EmployerController::class, 'cost'])->name("employer-cost");
});


Route::prefix('v1/front')->group(function () {});
