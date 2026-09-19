<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Models\Concerns;

use Fereydooni\LaravelTicketing\Models\Scopes\TicketingTenantScope;
use Fereydooni\LaravelTicketing\Support\Tenancy\TicketingTenancy;
use Illuminate\Database\Eloquent\Model;

/**
 * Scopes a ticketing model to the current tenant and stamps the tenant on create.
 *
 * Both behaviors are inert while tenancy is disabled.
 */
trait BelongsToTicketingTenant
{
    public static function bootBelongsToTicketingTenant(): void
    {
        static::addGlobalScope(new TicketingTenantScope());

        static::creating(function (Model $model): void {
            if (! TicketingTenancy::enabled()) {
                return;
            }

            $column = TicketingTenancy::column();

            if ($model->getAttribute($column) === null) {
                $model->setAttribute($column, TicketingTenancy::currentTenantId());
            }
        });
    }
}
