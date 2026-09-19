<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Integration\Tickets;

use Fereydooni\LaravelTicketing\Actions\Admin\SyncWorkflowConfigurationAction;
use Fereydooni\LaravelTicketing\Actions\Tickets\CreateTicketAction;
use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;
use Fereydooni\LaravelTicketing\Contracts\Search\SearchesTickets;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\Fixtures\FixedTenantResolver;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('ticketing.tenancy.enabled', true);
        $app['config']->set('ticketing.tenancy.resolver', FixedTenantResolver::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();
    }

    protected function tearDown(): void
    {
        FixedTenantResolver::$tenantId = null;

        parent::tearDown();
    }

    public function test_the_configured_resolver_supplies_the_tenant_context(): void
    {
        $this->assertInstanceOf(FixedTenantResolver::class, app(ResolvesTenantContext::class));
    }

    public function test_models_are_stamped_and_queries_scoped_to_the_current_tenant(): void
    {
        FixedTenantResolver::$tenantId = 'club-a';
        $statusA = Status::query()->create(['name' => 'Open A', 'slug' => 'open', 'is_default' => true]);
        $ticketA = app(CreateTicketAction::class)->create(['subject' => 'A'], $this->user());

        FixedTenantResolver::$tenantId = 'club-b';
        $statusB = Status::query()->create(['name' => 'Open B', 'slug' => 'open', 'is_default' => true]);
        $ticketB = app(CreateTicketAction::class)->create(['subject' => 'B'], $this->user());

        $this->assertSame('club-a', $statusA->tenant_id);
        $this->assertSame([$ticketB->getKey()], Ticket::query()->pluck('id')->all());
        $this->assertSame($statusB->getKey(), $ticketB->status_id, 'the default status must come from the same tenant');

        FixedTenantResolver::$tenantId = 'club-a';
        $this->assertSame([$ticketA->getKey()], Ticket::query()->pluck('id')->all());
        $this->assertSame($statusA->getKey(), $ticketA->status_id);
    }

    public function test_search_cannot_reach_another_tenant_even_with_a_tenant_filter(): void
    {
        FixedTenantResolver::$tenantId = 'club-a';
        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
        app(CreateTicketAction::class)->create(['subject' => 'A'], $this->user());

        FixedTenantResolver::$tenantId = 'club-b';

        $this->assertCount(0, app(SearchesTickets::class)->search(['tenant_id' => 'club-a']));
    }

    public function test_route_binding_does_not_resolve_another_tenants_ticket(): void
    {
        FixedTenantResolver::$tenantId = 'club-a';
        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
        $requester = $this->user();
        $ticket = app(CreateTicketAction::class)->create(['subject' => 'A'], $requester);

        FixedTenantResolver::$tenantId = 'club-b';

        $this->actingAs($requester)
            ->getJson('/tickets/' . $ticket->getKey())
            ->assertNotFound();
    }

    public function test_workflow_sync_is_idempotent_within_the_current_tenant(): void
    {
        FixedTenantResolver::$tenantId = 'club-a';
        $configuration = ['statuses' => [['name' => 'Open', 'slug' => 'open', 'is_default' => true]]];

        app(SyncWorkflowConfigurationAction::class)->sync($configuration);
        app(SyncWorkflowConfigurationAction::class)->sync($configuration);

        $this->assertSame(['club-a'], Status::query()->pluck('tenant_id')->all());
    }

    public function test_a_null_tenant_sees_only_platform_level_rows(): void
    {
        FixedTenantResolver::$tenantId = 'club-a';
        Status::query()->create(['name' => 'Open', 'slug' => 'open', 'is_default' => true]);
        app(CreateTicketAction::class)->create(['subject' => 'Club'], $this->user());

        FixedTenantResolver::$tenantId = null;
        Status::query()->create(['name' => 'Open', 'slug' => 'open-platform', 'is_default' => true]);
        $platform = app(CreateTicketAction::class)->create(['subject' => 'Platform'], $this->user());

        $this->assertSame([$platform->getKey()], Ticket::query()->pluck('id')->all());
    }
}
