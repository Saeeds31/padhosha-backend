<?php

use Illuminate\Support\Facades\Route;
use Modules\Portfolio\Http\Controllers\PortfolioController;

Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function () {
    Route::apiResource('portfolios', PortfolioController::class)->names('portfolio');
});
Route::prefix('v1/front')->group(function () {
    Route::get('portfolios', [PortfolioController::class,'frontIndex'])->name('portfolio-frontIndex');
    Route::get('portfolios/{id}', [PortfolioController::class,'frontDetail'])->name('portfolio-frontDetail');
    Route::get('portfolios/{id}/similar', [PortfolioController::class,'similar'])->name('portfolio-similar');
    
});
