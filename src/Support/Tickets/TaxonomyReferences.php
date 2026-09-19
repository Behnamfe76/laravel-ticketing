<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Support\Tickets;

use Fereydooni\LaravelTicketing\Models\Category;
use Fereydooni\LaravelTicketing\Models\Priority;
use Fereydooni\LaravelTicketing\Models\Status;
use Fereydooni\LaravelTicketing\Models\TicketType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Verifies taxonomy ids on ticket attributes point at existing rows.
 *
 * Lookups go through the models, so the tenant scope applies: an id belonging to another tenant
 * is rejected exactly like an id that does not exist. A database `exists` rule would bypass the
 * scope, which is why this is not a FormRequest rule.
 */
class TaxonomyReferences
{
    /**
     * @var array<string, class-string<Model>>
     */
    protected const MODELS = [
        'status_id' => Status::class,
        'priority_id' => Priority::class,
        'category_id' => Category::class,
        'type_id' => TicketType::class,
    ];

    /**
     * @param array<string, mixed> $attributes
     *
     * @throws ValidationException
     */
    public function assertExist(array $attributes): void
    {
        $errors = [];

        foreach (self::MODELS as $key => $model) {
            if (! isset($attributes[$key])) {
                continue;
            }

            $query = $model::query()->whereKey($attributes[$key]);

            if (in_array($key, ['category_id', 'type_id'], true)) {
                $query->where('is_active', true);
            }

            if (! $query->exists()) {
                $errors[$key] = "The selected {$key} is invalid.";
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
