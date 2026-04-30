<?php

declare(strict_types=1);

use Fereydooni\LaravelTicketing\Http\Controllers\Staff\TicketWorkflowController;
use Fereydooni\LaravelTicketing\Http\Controllers\Staff\TicketDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [TicketDashboardController::class, 'index'])->name('tickets.index');
Route::post('/', [TicketWorkflowController::class, 'store'])->name('tickets.store');
Route::post('/{ticket}/notes', [TicketWorkflowController::class, 'note'])->name('tickets.notes.store');
Route::post('/{ticket}/assignments', [TicketWorkflowController::class, 'assign'])->name('tickets.assignments.store');
Route::post('/{ticket}/resolve', [TicketWorkflowController::class, 'resolve'])->name('tickets.resolve');
