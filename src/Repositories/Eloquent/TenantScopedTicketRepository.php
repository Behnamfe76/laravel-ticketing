<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Repositories\Eloquent;

use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;
use Fereydooni\LaravelTicketing\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

class TenantScopedTicketRepository extends EloquentTicketRepository
{
    public function __construct(protected ResolvesTenantContext $tenantContext)
    {
    }

    public function query(): Builder
    {
        return Ticket::query()->where('tenant_id', $this->tenantContext->id());
    }
}
