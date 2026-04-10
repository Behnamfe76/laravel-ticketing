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
    ],

    'features' => [
        'portal' => true,
        'staff' => true,
        'api' => true,
        'mail' => false,
        'notifications' => true,
        'broadcasting' => false,
        'queues' => true,
        'attachments' => true,
        'multi_tenancy' => false,
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
        'enabled' => false,
        'resolver' => null,
        'column' => 'tenant_id',
    ],

    'mail' => [
        'inbound_enabled' => false,
        'mailbox' => null,
    ],

    'queue' => [
        'connection' => env('TICKETING_QUEUE_CONNECTION'),
        'queue' => env('TICKETING_QUEUE_NAME', 'ticketing'),
    ],
];
