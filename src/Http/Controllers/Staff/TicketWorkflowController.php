<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Controllers\Staff;

use Fereydooni\LaravelTicketing\Contracts\Tickets\AddsTicketReplies;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AssignsTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\CreatesTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\TransitionsTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\UpdatesTickets;
use Fereydooni\LaravelTicketing\Http\Controllers\Concerns\HandlesTicketParticipation;
use Fereydooni\LaravelTicketing\Http\Requests\Api\UpdateTicketRequest;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class TicketWorkflowController extends Controller
{
    use AuthorizesRequests;
    use HandlesTicketParticipation;

    public function store(Request $request, CreatesTickets $tickets): JsonResponse
    {
        $this->authorize('create', Ticket::class);

        $ticket = $tickets->create($request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]) + ['source' => 'staff'], $request->user());

        return response()->json(['data' => $ticket], 201);
    }

    public function note(Request $request, Ticket $ticket, AddsTicketReplies $replies): JsonResponse
    {
        $this->authorize('note', $ticket);

        $entry = $replies->add($ticket, $request->validate([
            'body' => ['required', 'string'],
        ]) + ['entry_type' => 'internal_note', 'source' => 'staff'], $request->user());

        return response()->json(['data' => $entry], 201);
    }

    public function assign(Request $request, Ticket $ticket, AssignsTickets $assignments): JsonResponse
    {
        $this->authorize('assign', $ticket);

        $assignment = $assignments->assign($ticket, $request->validate([
            'target_type' => ['required', 'string'],
            'target_id' => ['required'],
            'reason' => ['nullable', 'string'],
        ]), $request->user());

        return response()->json(['data' => $assignment], 201);
    }

    public function resolve(Request $request, Ticket $ticket, TransitionsTickets $transitions): JsonResponse
    {
        $this->authorize('manage', $ticket);

        return response()->json(['data' => $transitions->resolve($ticket, $request->user())]);
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket, UpdatesTickets $updates): JsonResponse
    {
        $this->authorize('manage', $ticket);

        return response()->json(['data' => $updates->update($ticket, $request->validated(), $request->user())]);
    }
}
