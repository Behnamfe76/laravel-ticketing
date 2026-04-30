<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Controllers\Portal;

use Fereydooni\LaravelTicketing\Contracts\Tickets\AddsTicketReplies;
use Fereydooni\LaravelTicketing\Contracts\Tickets\CreatesTickets;
use Fereydooni\LaravelTicketing\Http\Resources\Api\TicketResource;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Repositories\Eloquent\EloquentTicketRepository;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PortalTicketController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, EloquentTicketRepository $tickets)
    {
        return TicketResource::collection($tickets->visibleFor($request->user()));
    }

    public function show(Ticket $ticket): TicketResource
    {
        $this->authorize('view', $ticket);

        return new TicketResource($ticket->load(['conversationEntries', 'attachments']));
    }

    public function store(Request $request, CreatesTickets $tickets): JsonResponse
    {
        $this->authorize('create', Ticket::class);

        $ticket = $tickets->create($request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]) + ['source' => 'portal'], $request->user());

        return response()->json(['data' => $ticket], 201);
    }

    public function reply(Request $request, Ticket $ticket, AddsTicketReplies $replies): JsonResponse
    {
        $this->authorize('reply', $ticket);

        $entry = $replies->add($ticket, $request->validate([
            'body' => ['required', 'string'],
        ]) + ['entry_type' => 'public_reply', 'source' => 'portal'], $request->user());

        return response()->json(['data' => $entry], 201);
    }
}
