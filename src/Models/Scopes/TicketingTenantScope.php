<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models\Scopes;

use Fereydooni\LaravelTicketing\Support\Tenancy\TicketingTenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts every query on a tenant-owned ticketing model to the current tenant context.
 *
 * Applied as a global scope so repositories, route model binding, relations, and lookups such
 * as the default status are all covered without each call site remembering to filter. A null
 * tenant context matches only rows with a null tenant column (platform-level records); it never
 * falls back to "all tenants".
 */
class TicketingTenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! TicketingTenancy::enabled()) {
            return;
        }

        $column = $model->qualifyColumn(TicketingTenancy::column());
        $tenantId = TicketingTenancy::currentTenantId();

        if ($tenantId === null) {
            $builder->whereNull($column);

            return;
        }

        $builder->where($column, $tenantId);
    }
}
