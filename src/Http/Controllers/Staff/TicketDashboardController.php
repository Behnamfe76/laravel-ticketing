<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Controllers\Staff;

use Fereydooni\LaravelTicketing\Contracts\Search\SearchesTickets;
use Fereydooni\LaravelTicketing\Http\Resources\Api\TicketResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TicketDashboardController extends Controller
{
    public function index(Request $request, SearchesTickets $tickets)
    {
        return TicketResource::collection($tickets->search($request->query(), $request->user()));
    }
}
