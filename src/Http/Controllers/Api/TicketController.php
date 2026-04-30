<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Controllers\Api;

use Fereydooni\LaravelTicketing\Contracts\Search\SearchesTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AddsTicketReplies;
use Fereydooni\LaravelTicketing\Contracts\Tickets\AssignsTickets;
use Fereydooni\LaravelTicketing\Contracts\Tickets\CreatesTickets;
use Fereydooni\LaravelTicketing\Http\Requests\Api\AddReplyRequest;
use Fereydooni\LaravelTicketing\Http\Requests\Api\AssignTicketRequest;
use Fereydooni\LaravelTicketing\Http\Requests\Api\CreateTicketRequest;
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

class TicketController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, SearchesTickets $tickets): AnonymousResourceCollection
    {
        return TicketResource::collection($tickets->search($request->query(), $request->user()));
    }

    public function store(CreateTicketRequest $request, CreatesTickets $tickets): JsonResponse
    {
        $this->authorize('create', Ticket::class);

        $ticket = $tickets->create($request->validated() + ['source' => 'api'], $request->user());

        return (new TicketResource($ticket))->response()->setStatusCode(201);
    }

    public function show(Ticket $ticket): TicketResource
    {
        $this->authorize('view', $ticket);

        return new TicketResource($ticket->load(['conversationEntries', 'assignments', 'tags']));
    }

    public function replies(AddReplyRequest $request, Ticket $ticket, AddsTicketReplies $replies): JsonResponse
    {
        $ability = ($request->validated()['entry_type'] ?? 'public_reply') === 'internal_note' ? 'note' : 'reply';
        $this->authorize($ability, $ticket);

        $entry = $replies->add($ticket, $request->validated() + ['source' => 'api'], $request->user());

        return response()->json(['data' => $entry], 201);
    }

    public function assignments(AssignTicketRequest $request, Ticket $ticket, AssignsTickets $assignments): JsonResponse
    {
        $this->authorize('assign', $ticket);

        $assignment = $assignments->assign($ticket, $request->validated(), $request->user());

        return response()->json(['data' => $assignment], 201);
    }

    public function transition(Request $request, Ticket $ticket): TicketResource
    {
        $this->authorize('manage', $ticket);

        if ($request->input('transition') === 'reopen') {
            $ticket->reopen();
        } else {
            $ticket->markResolved();
        }

        return new TicketResource($ticket->refresh());
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
