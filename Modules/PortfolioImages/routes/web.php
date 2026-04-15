<?php

use Illuminate\Support\Facades\Route;
use Modules\PortfolioImages\Http\Controllers\PortfolioImagesController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('portfolioimages', PortfolioImagesController::class)->names('portfolioimages');
});
