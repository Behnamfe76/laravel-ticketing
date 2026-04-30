<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Actions\Reporting;

use Fereydooni\LaravelTicketing\Contracts\Reporting\PublishesTicketMetrics;
use Fereydooni\LaravelTicketing\DTOs\Reporting\TicketMetricSnapshot;
use Fereydooni\LaravelTicketing\Events\TicketMetricsPublished;
use Fereydooni\LaravelTicketing\Models\Ticket;

class PublishTicketMetricsAction implements PublishesTicketMetrics
{
    public function publish(): TicketMetricSnapshot
    {
        $byStatus = Ticket::query()
            ->leftJoin('ticketing_statuses', 'ticketing_tickets.status_id', '=', 'ticketing_statuses.id')
            ->selectRaw('coalesce(ticketing_statuses.slug, "unknown") as status_slug, count(*) as aggregate')
            ->groupBy('status_slug')
            ->pluck('aggregate', 'status_slug')
            ->map(fn ($count) => (int) $count)
            ->all();

        $snapshot = new TicketMetricSnapshot(
            totalTickets: Ticket::query()->count(),
            openTickets: array_sum(array_intersect_key($byStatus, array_flip(['open', 'new', 'pending', 'unknown']))),
            resolvedTickets: array_sum(array_intersect_key($byStatus, array_flip(['resolved', 'closed']))),
            byStatus: $byStatus,
        );

        TicketMetricsPublished::dispatch($snapshot);

        return $snapshot;
    }
}
