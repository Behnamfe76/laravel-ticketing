<?php

declare(strict_types=1);

use Fereydooni\LaravelTicketing\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
Route::post('/tickets/{ticket}/replies', [TicketController::class, 'replies'])->name('tickets.replies.store');
Route::post('/tickets/{ticket}/assignments', [TicketController::class, 'assignments'])->name('tickets.assignments.store');
Route::post('/tickets/{ticket}/status-transitions', [TicketController::class, 'transition'])->name('tickets.status-transitions.store');
Route::get('/metadata', [TicketController::class, 'metadata'])->name('metadata.index');
