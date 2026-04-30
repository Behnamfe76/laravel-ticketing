<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Support\Forms;

use Fereydooni\LaravelTicketing\Models\CustomFieldDefinition;
use Illuminate\Support\Collection;

class CustomFieldFormBuilder
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function schema(string $scope = 'ticket'): Collection
    {
        return CustomFieldDefinition::query()
            ->where('scope', $scope)
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get()
            ->map(fn (CustomFieldDefinition $field) => [
                'slug' => $field->slug,
                'label' => $field->label,
                'type' => $field->field_type,
                'required' => $field->is_required,
                'options' => is_string($field->options) ? (json_decode($field->options, true) ?: []) : ($field->options ?? []),
                'visibility_rules' => is_string($field->visibility_rules) ? (json_decode($field->visibility_rules, true) ?: []) : ($field->visibility_rules ?? []),
            ]);
    }
}
