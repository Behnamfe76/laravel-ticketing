<?php

declare(strict_types=1);

use Fereydooni\LaravelTicketing\Http\Controllers\Portal\PortalTicketController;
use Illuminate\Support\Facades\Route;

Route::post('/', [PortalTicketController::class, 'store'])->name('tickets.store');
Route::get('/', [PortalTicketController::class, 'index'])->name('tickets.index');
Route::get('/{ticket}', [PortalTicketController::class, 'show'])->name('tickets.show');
Route::post('/{ticket}/replies', [PortalTicketController::class, 'reply'])->name('tickets.replies.store');
Route::get('/{ticket}/attachments/{attachment}', [PortalTicketController::class, 'downloadAttachment'])->name('tickets.attachments.show');
