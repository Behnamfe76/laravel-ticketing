<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Automation;

use Fereydooni\LaravelTicketing\Models\SLAPolicy;
use Fereydooni\LaravelTicketing\Models\Ticket;

interface ResolvesSLAPolicies
{
    public function resolveFor(Ticket $ticket): ?SLAPolicy;
}
