<?php

use Illuminate\Support\Facades\Route;
use Modules\Employer\Http\Controllers\EmployerController;

Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function () {
    Route::apiResource('employers', EmployerController::class)->names('employer');
    Route::post('employers-comment', [EmployerController::class,'EmployerComment'])->name('employer');
});
Route::prefix('v1/front')->group(function () {
});
