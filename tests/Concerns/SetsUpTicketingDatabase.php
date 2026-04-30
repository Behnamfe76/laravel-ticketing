<?php

declare(strict_types=1);

namespace Fereydooni\LaravelTicketing\Tests\Concerns;

use Fereydooni\LaravelTicketing\Tests\Fixtures\TicketingUser;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

trait SetsUpTicketingDatabase
{
    protected function migrateTicketingDatabase(): void
    {
        Artisan::call('migrate');

        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * @param array<int, string> $abilities
     */
    protected function user(array $abilities = []): TicketingUser
    {
        return TicketingUser::query()->create([
            'name' => 'Test User',
            'email' => uniqid('user', true) . '@example.test',
        ])->withTicketingAbilities($abilities);
    }
}
