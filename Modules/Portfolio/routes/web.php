<?php

use Illuminate\Support\Facades\Route;
use Modules\Portfolio\Http\Controllers\PortfolioController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('portfolios', PortfolioController::class)->names('portfolio');
});
