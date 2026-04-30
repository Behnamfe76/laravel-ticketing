<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Unit\Integration;

use Fereydooni\LaravelTicketing\Actions\Reporting\PublishTicketMetricsAction;
use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Models\EmailThread;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Services\Email\InboundTicketMailProcessor;
use Fereydooni\LaravelTicketing\Services\Tenancy\TenantContext;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class ExternalIntegrationRulesTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
        Status::query()->create(['name' => 'Resolved', 'slug' => 'resolved']);
    }

    public function test_inbound_mail_creates_and_threads_ticket_replies(): void
    {
        $thread = app(InboundTicketMailProcessor::class)->process([
            'message_id' => '<first@example.test>',
            'from' => 'customer@example.test',
            'to' => ['support@example.test'],
            'subject' => 'Email issue',
            'body' => 'Please help',
        ]);

        $replyThread = app(InboundTicketMailProcessor::class)->process([
            'message_id' => '<reply@example.test>',
            'in_reply_to' => '<first@example.test>',
            'from' => 'customer@example.test',
            'subject' => 'Re: Email issue',
            'body' => 'More context',
        ]);

        $this->assertSame($thread->ticket_id, $replyThread->ticket_id);
        $this->assertSame(2, EmailThread::query()->count());
        $this->assertCount(1, $thread->ticket->conversationEntries);
    }

    public function test_reporting_payload_and_tenant_context_snapshot_are_stable(): void
    {
        app(TenantContext::class)->set('tenant-x', ['source' => 'test']);
        app(CreateTicketAction::class)->create(['subject' => 'Metric ticket'], $this->user());

        $snapshot = app(PublishTicketMetricsAction::class)->publish();
        $state = app(TenantContext::class)->snapshot();

        app(TenantContext::class)->clear();
        app(TenantContext::class)->restore($state);

        $this->assertSame(1, $snapshot->totalTickets);
        $this->assertSame(['source' => 'test'], app(TenantContext::class)->payload());
        $this->assertSame('tenant-x', app(TenantContext::class)->id());
    }
}
