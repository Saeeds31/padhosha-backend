<?php

use Illuminate\Support\Facades\Route;
use Modules\PortfolioTechnology\Http\Controllers\PortfolioTechnologyController;

Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function () {
    Route::apiResource('portfolio-technologies', PortfolioTechnologyController::class)->names('portfoliotechnology');
});
