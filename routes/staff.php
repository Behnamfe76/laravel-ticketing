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
Route::patch('/{ticket}', [TicketWorkflowController::class, 'update'])->name('tickets.update');
Route::post('/{ticket}/watchers', [TicketWorkflowController::class, 'watch'])->name('tickets.watchers.store');
Route::delete('/{ticket}/watchers', [TicketWorkflowController::class, 'unwatch'])->name('tickets.watchers.destroy');
Route::get('/{ticket}/attachments/{attachment}', [TicketWorkflowController::class, 'downloadAttachment'])->name('tickets.attachments.show');
