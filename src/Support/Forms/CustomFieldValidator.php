<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Support\Forms;

use Fereydooni\LaravelTicketing\Models\CustomFieldDefinition;
use Illuminate\Support\MessageBag;

class CustomFieldValidator
{
    /**
     * @param iterable<CustomFieldDefinition> $definitions
     * @param array<string, mixed> $input
     */
    public function validate(iterable $definitions, array $input): MessageBag
    {
        $errors = new MessageBag();

        foreach ($definitions as $definition) {
            if ($definition->is_active === false || ! $this->visible($definition, $input)) {
                continue;
            }

            $value = $input[$definition->slug] ?? null;

            if ($definition->is_required && blank($value)) {
                $errors->add($definition->slug, 'The ' . $definition->label . ' field is required.');
                continue;
            }

            if (! blank($value) && ! $this->matchesType($definition->field_type, $value)) {
                $errors->add($definition->slug, 'The ' . $definition->label . ' field must be a valid ' . $definition->field_type . '.');
            }
        }

        return $errors;
    }

    public function normalize(CustomFieldDefinition $definition, mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($definition->field_type) {
            'number' => (string) (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            default => trim((string) $value),
        };
    }

    /**
     * @param array<string, mixed> $input
     */
    protected function visible(CustomFieldDefinition $definition, array $input): bool
    {
        $rules = $definition->visibility_rules ?? [];

        if (is_string($rules)) {
            $rules = json_decode($rules, true) ?: [];
        }

        foreach ($rules as $field => $expected) {
            if (($input[$field] ?? null) !== $expected) {
                return false;
            }
        }

        return true;
    }

    protected function matchesType(string $type, mixed $value): bool
    {
        return match ($type) {
            'number' => is_numeric($value),
            'boolean' => is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false'], true),
            'select' => is_scalar($value),
            default => is_string($value) || is_numeric($value),
        };
    }
}
