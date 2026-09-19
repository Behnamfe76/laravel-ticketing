<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Fixtures;

use Fereydooni\LaravelTicketing\Contracts\MultiTenancy\ResolvesTenantContext;

/**
 * Stands in for a host tenancy layer supplying the current tenant id.
 */
class FixedTenantResolver implements ResolvesTenantContext
{
    public static int|string|null $tenantId = null;

    public function id(): int|string|null
    {
        return static::$tenantId;
    }

    public function payload(): array
    {
        return [];
    }
}
