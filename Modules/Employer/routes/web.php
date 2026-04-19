<?php

use Illuminate\Support\Facades\Route;
use Modules\Employer\Http\Controllers\EmployerController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('employers', EmployerController::class)->names('employer');
});
