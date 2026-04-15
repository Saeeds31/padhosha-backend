<?php

use Illuminate\Support\Facades\Route;
use Modules\PortfolioCategory\Http\Controllers\PortfolioCategoryController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('portfoliocategories', PortfolioCategoryController::class)->names('portfoliocategory');
});
