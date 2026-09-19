<?php

declare(strict_types=1);

return [
    'models' => [
        'ticket' => \Fereydooni\LaravelTicketing\Models\Ticket::class,
        'conversation_entry' => \Fereydooni\LaravelTicketing\Models\ConversationEntry::class,
        'attachment' => \Fereydooni\LaravelTicketing\Models\Attachment::class,
        'assignment' => \Fereydooni\LaravelTicketing\Models\Assignment::class,
        'queue' => \Fereydooni\LaravelTicketing\Models\Queue::class,
        'team' => \Fereydooni\LaravelTicketing\Models\Team::class,
        'watcher' => \Fereydooni\LaravelTicketing\Models\Watcher::class,
        'audit_record' => \Fereydooni\LaravelTicketing\Models\AuditRecord::class,
        'custom_field_definition' => \Fereydooni\LaravelTicketing\Models\CustomFieldDefinition::class,
        'custom_field_value' => \Fereydooni\LaravelTicketing\Models\CustomFieldValue::class,
        'sla_policy' => \Fereydooni\LaravelTicketing\Models\SLAPolicy::class,
        'automation_rule' => \Fereydooni\LaravelTicketing\Models\AutomationRule::class,
        'saved_view' => \Fereydooni\LaravelTicketing\Models\SavedView::class,
        'tag' => \Fereydooni\LaravelTicketing\Models\Tag::class,
        'email_thread' => \Fereydooni\LaravelTicketing\Models\EmailThread::class,
    ],

    // The HTTP adapters are opt-in. A route group is registered only when its feature switch
    // here AND its `routes.*.enabled` flag are both true.
    'features' => [
        'portal' => false,
        'staff' => false,
        'api' => false,
        'mail' => false,
        'notifications' => true,
        'broadcasting' => false,
        'queues' => true,
        'attachments' => true,
        'multi_tenancy' => false,
    ],

    'migrations' => [
        // Load the package migrations into the default migrator. Turn off when you publish them
        // to your own path(s) instead, e.g. to run them per tenant database.
        'load' => true,
    ],

    'notifications' => [
        // Channels used by the built-in notifications while `features.notifications` is true.
        'channels' => ['database', 'mail'],
    ],

    'routes' => [
        'portal' => [
            'enabled' => true,
            'prefix' => 'tickets',
            'middleware' => ['web', 'auth'],
        ],
        'staff' => [
            'enabled' => true,
            'prefix' => 'staff/tickets',
            'middleware' => ['web', 'auth'],
        ],
        'api' => [
            'enabled' => true,
            'prefix' => 'api/ticketing',
            'middleware' => ['api', 'auth'],
        ],
    ],

    // Uploads are stored privately on this disk and served through the adapters' authorized
    // download routes. `visibility` and `signed_urls` are reserved and not used yet.
    'attachments' => [
        'disk' => env('TICKETING_ATTACHMENTS_DISK', env('FILESYSTEM_DISK', 'local')),
        'directory' => env('TICKETING_ATTACHMENTS_DIRECTORY', 'ticketing'),
        'max_upload_size_kb' => 10240,
        'visibility' => 'private',
        'signed_urls' => true,
    ],

    'auth' => [
        'guard' => null,
        'user_model' => null,
        'morph_name' => 'actor',
    ],

    'tenancy' => [
        // Scope every package model to the current tenant and stamp it on create. Set `resolver`
        // to a ResolvesTenantContext class to supply the tenant id from your own tenancy layer.
        'enabled' => false,
        'resolver' => null,
        'column' => 'tenant_id',
    ],

    'mail' => [
        'inbound_enabled' => false,
        'default_mailbox' => env('TICKETING_DEFAULT_MAILBOX', 'support'),
        'mailbox' => null,
        'from' => [
            'address' => env('TICKETING_MAIL_FROM_ADDRESS', 'support@example.test'),
            'name' => env('TICKETING_MAIL_FROM_NAME', 'Support'),
        ],
        'quarantine_unmatched' => true,
    ],

    'queue' => [
        'connection' => env('TICKETING_QUEUE_CONNECTION'),
        'queue' => env('TICKETING_QUEUE_NAME', 'ticketing'),
    ],
];
