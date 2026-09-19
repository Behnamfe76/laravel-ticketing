<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Support\Tenancy;

use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;

/**
 * Reads the package tenancy configuration in one place.
 *
 * Tenancy is enabled by either `ticketing.tenancy.enabled` or the older
 * `ticketing.features.multi_tenancy` switch, so hosts that followed the 1.x README keep working.
 */
final class TicketingTenancy
{
    public static function enabled(): bool
    {
        return (bool) config('ticketing.tenancy.enabled', false)
            || (bool) config('ticketing.features.multi_tenancy', false);
    }

    public static function column(): string
    {
        return (string) config('ticketing.tenancy.column', 'tenant_id');
    }

    public static function currentTenantId(): int|string|null
    {
        return app(ResolvesTenantContext::class)->id();
    }
}
