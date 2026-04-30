<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Reporting;

use Fereydooni\LaravelTicketing\DTOs\Reporting\TicketMetricSnapshot;

interface PublishesTicketMetrics
{
    public function publish(): TicketMetricSnapshot;
}
