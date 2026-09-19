<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Support\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * The polymorphic type stored for an actor, matching what visibility queries compare against.
 */
final class ActorType
{
    public static function of(?Authenticatable $actor): ?string
    {
        if ($actor === null) {
            return null;
        }

        return $actor instanceof Model ? $actor->getMorphClass() : $actor::class;
    }
}
