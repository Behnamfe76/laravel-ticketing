<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Feature\Auth;

use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Policies\TicketPolicy;
use Fereydooni\LaravelTicketing\Support\Auth\ActorType;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\Fixtures\TicketingUser;
use Fereydooni\LaravelTicketing\Tests\TestCase;
use Illuminate\Auth\GenericUser;
use Illuminate\Database\Eloquent\Relations\Relation;

class TicketVisibilityTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();

        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
    }

    protected function tearDown(): void
    {
        Relation::morphMap([], false);

        parent::tearDown();
    }

    public function test_a_stranger_cannot_view_or_reply_to_someone_elses_ticket(): void
    {
        $requester = $this->user();
        $stranger = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Private'], $requester);
        $policy = app(TicketPolicy::class);

        $this->assertFalse($policy->view($stranger, $ticket));
        $this->assertFalse($policy->reply($stranger, $ticket));
        $this->assertTrue($policy->create($stranger));
    }

    public function test_a_stranger_gets_forbidden_from_the_portal_and_api(): void
    {
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Private'], $this->user());
        $stranger = $this->user();

        $this->actingAs($stranger)->getJson('/tickets/' . $ticket->getKey())->assertForbidden();
        $this->actingAs($stranger)->postJson('/tickets/' . $ticket->getKey() . '/replies', ['body' => 'Hi'])->assertForbidden();
        $this->actingAs($stranger)->getJson('/api/ticketing/tickets/' . $ticket->getKey())->assertForbidden();
    }

    public function test_requester_abilities_come_from_the_permissions_config(): void
    {
        config()->set('ticketing-permissions.roles.requester', ['ticket.create', 'ticket.view']);
        $requester = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Read only'], $requester);
        $policy = app(TicketPolicy::class);

        $this->assertTrue($policy->view($requester, $ticket));
        $this->assertFalse($policy->reply($requester, $ticket));
    }

    public function test_staff_view_any_is_granted_only_by_the_host(): void
    {
        $policy = app(TicketPolicy::class);

        $this->assertFalse($policy->viewAny($this->user()));
        $this->assertTrue($policy->viewAny($this->user(['ticket.view_any'])));
    }

    public function test_actors_are_matched_by_morph_alias_when_the_host_uses_a_morph_map(): void
    {
        Relation::morphMap(['ticketing-user' => TicketingUser::class]);
        $requester = $this->user();

        $ticket = app(CreateTicketAction::class)->create(['subject' => 'Aliased'], $requester);

        $this->assertSame('ticketing-user', $ticket->requester_type);
        $this->actingAs($requester)
            ->getJson('/tickets')
            ->assertOk()
            ->assertJsonPath('data.0.subject', 'Aliased');
        $this->assertTrue(app(TicketPolicy::class)->view($requester, $ticket));
    }

    public function test_actor_type_supports_non_eloquent_authenticatables(): void
    {
        $this->assertSame(GenericUser::class, ActorType::of(new GenericUser(['id' => 1])));
        $this->assertNull(ActorType::of(null));
    }
}
