<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'subject' => $this->subject,
            'description' => $this->description,
            'status_id' => $this->status_id,
            'priority_id' => $this->priority_id,
            'category_id' => $this->category_id,
            'source' => $this->source,
            'resolved_at' => $this->resolved_at?->toISOString(),
            'last_activity_at' => $this->last_activity_at?->toISOString(),
        ];
    }
}
