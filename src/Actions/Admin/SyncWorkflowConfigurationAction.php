<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Actions\Admin;

use Fereydooni\LaravelTicketing\Models\Category;
use Fereydooni\LaravelTicketing\Models\CustomFieldDefinition;
use Fereydooni\LaravelTicketing\Models\Priority;
use Fereydooni\LaravelTicketing\Models\Queue;
use Fereydooni\LaravelTicketing\Models\SavedView;
use Fereydooni\LaravelTicketing\Models\SLAPolicy;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\Tag;
use Fereydooni\LaravelTicketing\Support\Tenancy\TicketingTenancy;
use Illuminate\Support\Facades\DB;

class SyncWorkflowConfigurationAction
{
    /**
     * @param array<string, mixed> $configuration
     */
    public function sync(array $configuration): void
    {
        DB::transaction(function () use ($configuration): void {
            $this->upsert(Status::class, $configuration['statuses'] ?? []);
            $this->upsert(Priority::class, $configuration['priorities'] ?? []);
            $this->upsert(Category::class, $configuration['categories'] ?? []);
            $this->upsert(Queue::class, $configuration['queues'] ?? []);
            $this->upsert(Tag::class, $configuration['tags'] ?? []);
            $this->upsert(SLAPolicy::class, $configuration['sla_policies'] ?? []);
            $this->upsert(SavedView::class, $configuration['saved_views'] ?? []);
            $this->upsert(CustomFieldDefinition::class, $configuration['custom_fields'] ?? []);
        });
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $model
     * @param iterable<array<string, mixed>> $rows
     */
    protected function upsert(string $model, iterable $rows): void
    {
        // With tenancy on, rows belong to the current tenant; keying them on a null tenant would
        // miss the scoped row on every re-sync and insert a duplicate.
        $tenantId = TicketingTenancy::enabled() ? TicketingTenancy::currentTenantId() : null;

        foreach ($rows as $row) {
            $model::query()->updateOrCreate([
                'tenant_id' => $row['tenant_id'] ?? $tenantId,
                'slug' => $row['slug'],
            ], $row);
        }
    }
}
