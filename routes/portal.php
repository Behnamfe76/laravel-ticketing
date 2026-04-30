<?php

declare(strict_types=1);

use Fereydooni\LaravelTicketing\Http\Controllers\Portal\PortalTicketController;
use Illuminate\Support\Facades\Route;

Route::post('/', [PortalTicketController::class, 'store'])->name('tickets.store');
Route::post('/{ticket}/replies', [PortalTicketController::class, 'reply'])->name('tickets.replies.store');
