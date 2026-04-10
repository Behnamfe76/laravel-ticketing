<?php

declare(strict_types=1);

return [
    'abilities' => [
        'ticket.create' => 'Create tickets from staff, portal, or workflow entry points.',
        'ticket.view' => 'View ticket details within the actor visibility scope.',
        'ticket.reply' => 'Add public replies to a ticket.',
        'ticket.note' => 'Add internal notes to a ticket.',
        'ticket.assign' => 'Assign tickets to a user, team, or queue.',
        'ticket.manage' => 'Update ticket status, taxonomy, and metadata.',
        'ticket.configure' => 'Manage workflow configuration, taxonomy, and queue settings.',
        'ticket.report' => 'Read reporting hooks and operational metrics.',
    ],

    'roles' => [
        'requester' => [
            'ticket.create',
            'ticket.view',
            'ticket.reply',
        ],
        'agent' => [
            'ticket.view',
            'ticket.reply',
            'ticket.note',
            'ticket.assign',
            'ticket.manage',
        ],
        'administrator' => [
            'ticket.view',
            'ticket.reply',
            'ticket.note',
            'ticket.assign',
            'ticket.manage',
            'ticket.configure',
            'ticket.report',
        ],
    ],
];
