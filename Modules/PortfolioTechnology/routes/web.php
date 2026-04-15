<?php

use Illuminate\Support\Facades\Route;
use Modules\PortfolioTechnology\Http\Controllers\PortfolioTechnologyController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('portfoliotechnologies', PortfolioTechnologyController::class)->names('portfoliotechnology');
});
