<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Unit\Tickets;

use Fereydooni\LaravelTicketing\Actions\Assignments\AssignTicketAction;
use Fereydooni\LaravelTicketing\Actions\Replies\AddReplyAction;
use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class TicketLifecycleTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
    }

    public function test_ticket_can_be_created_replied_to_assigned_resolved_and_reopened(): void
    {
        $actor = $this->user(['ticket.assign', 'ticket.manage']);

        $ticket = app(CreateTicketAction::class)->create([
            'subject' => 'Printer down',
            'description' => 'The office printer is unavailable.',
        ], $actor);

        $reply = app(AddReplyAction::class)->add($ticket, [
            'body' => 'We are checking this now.',
        ], $actor);

        $assignment = app(AssignTicketAction::class)->assign($ticket, [
            'target_type' => 'user',
            'target_id' => $actor->getKey(),
        ], $actor);

        $ticket->markResolved();
        $ticket->reopen();

        $this->assertSame('Printer down', $ticket->refresh()->subject);
        $this->assertSame('public_reply', $reply->entry_type);
        $this->assertTrue($assignment->is_current);
        $this->assertNull($ticket->resolved_at);
        $this->assertCount(3, $ticket->auditRecords);
    }
}
