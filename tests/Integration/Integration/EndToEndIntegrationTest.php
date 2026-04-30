<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Integration\Integration;

use Fereydooni\LaravelTicketing\Contracts\Mail\ProcessesInboundTicketMail;
use Fereydooni\LaravelTicketing\Contracts\Reporting\PublishesTicketMetrics;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class EndToEndIntegrationTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
    }

    public function test_email_created_ticket_can_be_managed_through_api_and_reported(): void
    {
        $admin = $this->user(['ticket.assign', 'ticket.manage', 'ticket.note']);

        $thread = app(ProcessesInboundTicketMail::class)->process([
            'message_id' => '<e2e@example.test>',
            'from' => 'customer@example.test',
            'to' => ['support@example.test'],
            'subject' => 'End to end',
            'body' => 'Created from email',
        ]);

        $this->actingAs($admin)
            ->postJson('/api/ticketing/tickets/' . $thread->ticket_id . '/assignments', [
                'target_type' => 'user',
                'target_id' => $admin->getKey(),
            ])
            ->assertCreated();

        $this->actingAs($admin)
            ->postJson('/api/ticketing/tickets/' . $thread->ticket_id . '/status-transitions', ['transition' => 'resolve'])
            ->assertOk();

        $snapshot = app(PublishesTicketMetrics::class)->publish();

        $this->assertNotNull(Ticket::query()->findOrFail($thread->ticket_id)->resolved_at);
        $this->assertSame(1, $snapshot->totalTickets);
    }
}
