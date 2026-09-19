<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Controllers\Staff;

use Fereydooni\LaravelTicketing\Contracts\Search\SearchesTickets;
use Fereydooni\LaravelTicketing\Http\Resources\Api\TicketResource;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TicketDashboardController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, SearchesTickets $tickets)
    {
        $this->authorize('viewAny', Ticket::class);

        return TicketResource::collection($tickets->search($request->query(), $request->user()));
    }
}
