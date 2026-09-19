<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Controllers\Api;

use Fereydooni\LaravelTicketing\Contracts\Auth\MapsTicketRoles;
use Fereydooni\LaravelTicketing\Contracts\Search\SearchesTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AddsTicketReplies;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AssignsTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\CreatesTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\TransitionsTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\UpdatesTickets;
use Fereydooni\LaravelTicketing\Http\Controllers\Concerns\HandlesTicketParticipation;
use Fereydooni\LaravelTicketing\Http\Requests\Api\AddReplyRequest;
use Fereydooni\LaravelTicketing\Http\Requests\Api\AssignTicketRequest;
use Fereydooni\LaravelTicketing\Http\Requests\Api\CreateTicketRequest;
use Fereydooni\LaravelTicketing\Http\Requests\Api\UpdateTicketRequest;
use Fereydooni\LaravelTicketing\Http\Resources\Api\TicketResource;
use Fereydooni\LaravelTicketing\Models\Category;
use Fereydooni\LaravelTicketing\Models\CustomFieldDefinition;
use Fereydooni\LaravelTicketing\Models\Priority;
use Fereydooni\LaravelTicketing\Models\Queue;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;

class TicketController extends Controller
{
    use AuthorizesRequests;
    use HandlesTicketParticipation;

    public function index(Request $request, SearchesTickets $tickets): AnonymousResourceCollection
    {
        return TicketResource::collection($tickets->search($request->query(), $request->user()));
    }

    /**
     * Requesters may pick a category. Priority and type are triage decisions and are kept only
     * for actors with `ticket.manage`.
     */
    public function store(CreateTicketRequest $request, CreatesTickets $tickets, MapsTicketRoles $roles): JsonResponse
    {
        $this->authorize('create', Ticket::class);

        $attributes = Arr::except($request->validated(), ['files']);

        if (! $roles->allows($request->user(), 'ticket.manage')) {
            $attributes = Arr::except($attributes, ['priority_id', 'type_id']);
        }

        $ticket = $tickets->create($attributes + [
            'source' => 'api',
            'uploads' => $request->file('files', []),
        ], $request->user());

        return (new TicketResource($ticket))->response()->setStatusCode(201);
    }

    public function show(Ticket $ticket): TicketResource
    {
        $this->authorize('view', $ticket);

        return new TicketResource($ticket->load(['conversationEntries.attachments', 'attachments', 'assignments', 'tags']));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket, UpdatesTickets $updates): TicketResource
    {
        $this->authorize('manage', $ticket);

        return new TicketResource($updates->update($ticket, $request->validated(), $request->user()));
    }

    public function replies(AddReplyRequest $request, Ticket $ticket, AddsTicketReplies $replies): JsonResponse
    {
        $ability = ($request->validated()['entry_type'] ?? 'public_reply') === 'internal_note' ? 'note' : 'reply';
        $this->authorize($ability, $ticket);

        $entry = $replies->add($ticket, Arr::except($request->validated(), ['files']) + [
            'source' => 'api',
            'uploads' => $request->file('files', []),
        ], $request->user());

        return response()->json(['data' => $entry->load('attachments')], 201);
    }

    public function assignments(AssignTicketRequest $request, Ticket $ticket, AssignsTickets $assignments): JsonResponse
    {
        $this->authorize('assign', $ticket);

        $assignment = $assignments->assign($ticket, $request->validated(), $request->user());

        return response()->json(['data' => $assignment], 201);
    }

    public function transition(Request $request, Ticket $ticket, TransitionsTickets $transitions): TicketResource
    {
        $this->authorize('manage', $ticket);

        $data = $request->validate([
            'transition' => ['nullable', 'in:resolve,reopen,status'],
            'status_id' => ['required_if:transition,status', 'integer'],
        ]);

        $ticket = match ($data['transition'] ?? 'resolve') {
            'reopen' => $transitions->reopen($ticket, $request->user()),
            'status' => $transitions->changeStatus($ticket, $data['status_id'], $request->user()),
            default => $transitions->resolve($ticket, $request->user()),
        };

        return new TicketResource($ticket);
    }

    public function metadata(): JsonResponse
    {
        return response()->json([
            'statuses' => Status::query()->orderBy('sort_order')->get(),
            'priorities' => Priority::query()->orderBy('sort_order')->get(),
            'categories' => Category::query()->where('is_active', true)->get(),
            'queues' => Queue::query()->where('is_active', true)->get(),
            'custom_fields' => CustomFieldDefinition::query()->where('is_active', true)->orderBy('display_order')->get(),
        ]);
    }
}
