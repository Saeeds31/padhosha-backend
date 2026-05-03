<?php

use Illuminate\Support\Facades\Route;
use Modules\Ticket\Http\Controllers\TicketController;

Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function () {
    Route::apiResource('tickets', TicketController::class)->names('ticket');
    Route::post('messages', [TicketController::class,'sendMessage'])->name('ticket-sendMessage');
    Route::post('tickets/{id}/status', [TicketController::class,'changeStatus'])->name('ticket-changeStatus');
    Route::post('tickets/{id}/referd', [TicketController::class,'changeDoer'])->name('ticket-changeDoer');
    
});

Route::middleware(['auth:sanctum'])->prefix('v1/employer')->group(function () {
    Route::get('tickets', [TicketController::class, 'EmployerIndex'])->name('EmployerIndex');
    Route::post('tickets', [TicketController::class, 'EmployerStoreTicket'])->name('EmployerStoreTicket');
});
