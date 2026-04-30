<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticketing_statuses', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('slug');
            $table->string('kind')->default('open');
            $table->string('color')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_terminal')->default(false);
            $table->json('transitions')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('ticketing_priorities', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('slug');
            $table->unsignedInteger('sort_order')->default(0);
            $table->unsignedInteger('response_target_minutes')->nullable();
            $table->unsignedInteger('resolution_target_minutes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('ticketing_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('ticketing_types', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('ticketing_queues', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->nullableMorphs('default_assignee');
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('ticketing_teams', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('ticketing_tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->string('number')->unique();
            $table->string('subject');
            $table->text('description')->nullable();
            $table->nullableMorphs('requester');
            $table->nullableMorphs('creator');
            $table->foreignId('status_id')->nullable()->constrained('ticketing_statuses')->nullOnDelete();
            $table->foreignId('priority_id')->nullable()->constrained('ticketing_priorities')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('ticketing_categories')->nullOnDelete();
            $table->foreignId('type_id')->nullable()->constrained('ticketing_types')->nullOnDelete();
            $table->unsignedBigInteger('current_assignment_id')->nullable()->index();
            $table->unsignedBigInteger('sla_policy_id')->nullable()->index();
            $table->timestamp('first_response_due_at')->nullable();
            $table->timestamp('resolution_due_at')->nullable();
            $table->timestamp('first_responded_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->string('source')->default('workflow');
            $table->string('visibility')->default('private');
            $table->text('search_document')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status_id']);
            $table->index(['tenant_id', 'last_activity_at']);
        });

        Schema::create('ticketing_conversation_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained('ticketing_tickets')->cascadeOnDelete();
            $table->string('tenant_id')->nullable()->index();
            $table->nullableMorphs('author');
            $table->string('entry_type');
            $table->text('body')->nullable();
            $table->string('body_format')->nullable();
            $table->string('source')->default('system');
            $table->string('visibility_scope')->default('private');
            $table->foreignId('in_reply_to_id')->nullable()->constrained('ticketing_conversation_entries')->nullOnDelete();
            $table->string('email_message_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('ticketing_attachments', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->morphs('attachable');
            $table->nullableMorphs('uploaded_by');
            $table->string('disk');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum')->nullable();
            $table->string('visibility_scope')->default('private');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('ticketing_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained('ticketing_tickets')->cascadeOnDelete();
            $table->string('tenant_id')->nullable()->index();
            $table->string('target_type');
            $table->string('target_id');
            $table->nullableMorphs('assigned_by');
            $table->text('reason')->nullable();
            $table->boolean('is_current')->default(true);
            $table->timestamp('assigned_at');
            $table->timestamp('released_at')->nullable();
            $table->json('meta')->nullable();

            $table->index(['ticket_id', 'is_current']);
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('ticketing_watchers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ticket_id')->constrained('ticketing_tickets')->cascadeOnDelete();
            $table->string('tenant_id')->nullable()->index();
            $table->morphs('actor');
            $table->string('relation_type')->default('watcher');
            $table->json('notification_preferences')->nullable();
            $table->timestamps();

            $table->unique(['ticket_id', 'actor_type', 'actor_id', 'relation_type'], 'ticketing_watchers_unique_actor_relation');
        });

        Schema::create('ticketing_audit_records', function (Blueprint $table): void {
            $table->id();
            $table->string('tenant_id')->nullable()->index();
            $table->foreignId('ticket_id')->nullable()->constrained('ticketing_tickets')->nullOnDelete();
            $table->nullableMorphs('actor');
            $table->string('event_name');
            $table->nullableMorphs('subject');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('context')->nullable();
            $table->timestamp('occurred_at')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticketing_audit_records');
        Schema::dropIfExists('ticketing_watchers');
        Schema::dropIfExists('ticketing_assignments');
        Schema::dropIfExists('ticketing_attachments');
        Schema::dropIfExists('ticketing_conversation_entries');
        Schema::dropIfExists('ticketing_tickets');
        Schema::dropIfExists('ticketing_teams');
        Schema::dropIfExists('ticketing_queues');
        Schema::dropIfExists('ticketing_types');
        Schema::dropIfExists('ticketing_categories');
        Schema::dropIfExists('ticketing_priorities');
        Schema::dropIfExists('ticketing_statuses');
    }
};
