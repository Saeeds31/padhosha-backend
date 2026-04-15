<?php

use Illuminate\Support\Facades\Route;
use Modules\File\Http\Controllers\FileCategoryController;
use Modules\File\Http\Controllers\FileController;

Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function () {
    Route::apiResource('files', FileController::class)->names('file');
    Route::apiResource('file-categories', FileCategoryController::class)->names('file-categories');
});
