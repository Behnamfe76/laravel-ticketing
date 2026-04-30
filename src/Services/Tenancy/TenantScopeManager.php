<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Services\Tenancy;

use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenantScopeManager
{
    public function __construct(protected ResolvesTenantContext $tenantContext)
    {
    }

    public function apply(Builder $query, ?string $column = null): Builder
    {
        $column ??= (string) config('ticketing.tenancy.column', 'tenant_id');

        return $query->where($column, $this->tenantContext->id());
    }

    public function stamp(Model $model, ?string $column = null): Model
    {
        $column ??= (string) config('ticketing.tenancy.column', 'tenant_id');
        $model->setAttribute($column, $this->tenantContext->id());

        return $model;
    }
}
