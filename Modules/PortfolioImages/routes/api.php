<?php

use Illuminate\Support\Facades\Route;
use Modules\PortfolioImages\Http\Controllers\PortfolioImagesController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('portfolioimages', PortfolioImagesController::class)->names('portfolioimages');
});
