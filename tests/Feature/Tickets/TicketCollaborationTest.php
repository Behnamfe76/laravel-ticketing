<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Feature\Tickets;

use Fereydooni\LaravelTicketing\Actions\Assignments\AssignTicketAction;
use Fereydooni\LaravelTicketing\Actions\Replies\AddReplyAction;
use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class TicketCollaborationTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
    }

    public function test_replies_assignments_audit_records_and_attachment_metadata_are_persisted(): void
    {
        $actor = $this->user(['ticket.assign']);
        $ticket = app(CreateTicketAction::class)->create([
            'subject' => 'Attachment issue',
            'attachments' => [[
                'disk' => 'local',
                'path' => 'ticketing/example.txt',
                'original_name' => 'example.txt',
                'size_bytes' => 100,
            ]],
        ], $actor);

        app(AddReplyAction::class)->add($ticket, [
            'body' => 'Reply with attachment',
            'attachments' => [[
                'disk' => 'local',
                'path' => 'ticketing/reply.txt',
                'original_name' => 'reply.txt',
                'size_bytes' => 200,
            ]],
        ], $actor);

        app(AssignTicketAction::class)->assign($ticket, [
            'target_type' => 'user',
            'target_id' => $actor->getKey(),
        ], $actor);

        $this->assertCount(1, $ticket->attachments);
        $this->assertCount(1, $ticket->refresh()->conversationEntries);
        $this->assertCount(3, $ticket->auditRecords);
        $this->assertNotNull($ticket->current_assignment_id);
    }
}
