<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Feature\Admin;

use Fereydooni\LaravelTicketing\Actions\Admin\SyncWorkflowConfigurationAction;
use Fereydooni\LaravelTicketing\Models\CustomFieldDefinition;
use Fereydooni\LaravelTicketing\Models\SavedView;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Policies\TicketAdministrationPolicy;
use Fereydooni\LaravelTicketing\Tests\Concerns\SetsUpTicketingDatabase;
use Fereydooni\LaravelTicketing\Tests\TestCase;

class AdminWorkflowConfigurationTest extends TestCase
{
    use SetsUpTicketingDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->migrateTicketingDatabase();
    }

    public function test_admin_can_sync_metadata_custom_fields_and_saved_views(): void
    {
        $admin = $this->user(['ticket.configure']);

        $this->assertTrue(app(TicketAdministrationPolicy::class)->configure($admin));

        app(SyncWorkflowConfigurationAction::class)->sync([
            'statuses' => [['name' => 'Waiting', 'slug' => 'waiting', 'kind' => 'pending']],
            'saved_views' => [['name' => 'Waiting Queue', 'slug' => 'waiting-queue', 'scope' => 'global', 'filters' => ['search' => 'waiting']]],
            'custom_fields' => [['scope' => 'ticket', 'name' => 'Severity', 'slug' => 'severity', 'field_type' => 'select', 'label' => 'Severity', 'options' => ['low', 'high']]],
        ]);

        $this->assertDatabaseHas('ticketing_statuses', ['slug' => 'waiting']);
        $this->assertSame(['search' => 'waiting'], SavedView::query()->firstOrFail()->filters);
        $this->assertSame(['low', 'high'], CustomFieldDefinition::query()->firstOrFail()->options);
    }
}
