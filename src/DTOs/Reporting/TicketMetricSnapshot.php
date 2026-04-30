<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\DTOs\Reporting;

class TicketMetricSnapshot
{
    /**
     * @param array<string, int> $byStatus
     */
    public function __construct(
        public readonly int $totalTickets,
        public readonly int $openTickets,
        public readonly int $resolvedTickets,
        public readonly array $byStatus,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_tickets' => $this->totalTickets,
            'open_tickets' => $this->openTickets,
            'resolved_tickets' => $this->resolvedTickets,
            'by_status' => $this->byStatus,
        ];
    }
}
