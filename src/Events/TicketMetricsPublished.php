<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Events;

use Fereydooni\LaravelTicketing\DTOs\Reporting\TicketMetricSnapshot;
use Illuminate\Foundation\Events\Dispatchable;

class TicketMetricsPublished
{
    use Dispatchable;

    public function __construct(public readonly TicketMetricSnapshot $snapshot)
    {
    }
}
