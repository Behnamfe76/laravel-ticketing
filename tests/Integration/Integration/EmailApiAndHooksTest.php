<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Integration\Integration;

use Fereydooni\LaravelTicketing\Contracts\Mail\ProcessesInboundTicketMail;
use Fereydooni\LaravelTicketing\Contracts\Reporting\PublishesTicketMetrics;
use Fereydooni\LaravelTicketing\Events\TicketMetricsPublished;
use Fereydooni\LaravelTicketing\Models\EmailThread;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class EmailApiAndHooksTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
    }

    public function test_email_and_reporting_contracts_are_bound_and_dispatch_events(): void
    {
        Event::fake([TicketMetricsPublished::class]);

        $thread = app(ProcessesInboundTicketMail::class)->process([
            'message_id' => '<hook@example.test>',
            'from' => 'customer@example.test',
            'subject' => 'Hook email',
            'body' => 'Incoming body',
            'mailbox' => 'support',
        ]);
        $snapshot = app(PublishesTicketMetrics::class)->publish();

        $this->assertInstanceOf(EmailThread::class, $thread);
        $this->assertSame('support', $thread->meta['mailbox']);
        $this->assertSame(1, $snapshot->totalTickets);
        Event::assertDispatched(TicketMetricsPublished::class);
    }
}
