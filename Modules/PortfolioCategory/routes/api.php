<?php

use Illuminate\Support\Facades\Route;
use Modules\PortfolioCategory\Http\Controllers\PortfolioCategoryController;

Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function () {
    Route::apiResource('portfolio-categories', PortfolioCategoryController::class)->names('portfoliocategory');
});
