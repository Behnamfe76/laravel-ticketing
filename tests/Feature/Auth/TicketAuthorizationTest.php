<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Feature\Auth;

use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class TicketAuthorizationTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
    }

    public function test_portal_reply_requires_authorized_actor(): void
    {
        $requester = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Need help'], $requester);

        $this->postJson('/tickets/' . $ticket->getKey() . '/replies', ['body' => 'Update'])
            ->assertUnauthorized();
    }

    public function test_watcher_can_reply_to_visible_ticket(): void
    {
        $requester = $this->user();
        $watcher = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Need help'], $requester);
        $ticket->watchers()->create([
            'actor_type' => $watcher::class,
            'actor_id' => $watcher->getKey(),
            'relation_type' => 'watcher',
        ]);

        $this->actingAs($watcher)
            ->postJson('/tickets/' . $ticket->getKey() . '/replies', ['body' => 'Following up'])
            ->assertCreated();
    }
}
