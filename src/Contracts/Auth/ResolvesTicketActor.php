<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Contracts\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

interface ResolvesTicketActor
{
    public function resolve(Request $request): ?Authenticatable;
}
