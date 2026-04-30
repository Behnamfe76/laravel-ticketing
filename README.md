# Laravel Ticketing

Reusable Laravel package for support and service desk ticketing in existing Laravel
applications. The package provides a Laravel-native service provider, publishable
configuration and migrations, Eloquent models, lifecycle actions, policies, API and UI
adapters, email ingestion hooks, reporting hooks, and explicit extension contracts.

## Requirements

- PHP 8.3+
- Laravel 11 or 12 component versions
- A host application user model and authentication flow
- A relational database supported by Laravel migrations

## Installation

```bash
composer require fereydooni/laravel-ticketing
php artisan ticketing:install
php artisan migrate
```

The install command publishes `ticketing-config`, `ticketing-migrations`, and
`ticketing-lang`. Publish views separately when customizing the optional staff adapter:

```bash
php artisan vendor:publish --tag=ticketing-views
```

## Configuration

Review `config/ticketing.php` after publishing. The important sections are:

- `models`: configurable model map for package entities.
- `features`: opt-in switches for portal, staff, API, mail, notifications, queues,
  attachments, and multi-tenancy.
- `routes`: route prefixes and middleware for portal, staff, and API entry points.
- `attachments`: disk, directory, visibility, and signed URL behavior.
- `auth`: host auth model and guard integration hints.
- `tenancy`: tenant resolver and tenant column configuration.
- `mail`: inbound mailbox, outbound sender, and unmatched message behavior.
- `queue`: connection and queue name for ticketing work.

Abilities and default role mappings live in `config/ticketing-permissions.php`.
Host applications can replace the `MapsTicketRoles` binding to integrate with their own
authorization system.

## Incremental Adoption

1. Enable the core lifecycle first: ticket creation, replies, assignment, audit records,
   and notifications.
2. Add workflow configuration: statuses, priorities, categories, queues, tags, saved
   views, SLA policies, automation rules, and custom fields.
3. Enable integrations as needed: REST API routes, inbound email, outbound thread
   recording, reporting hooks, staff dashboard routes, and portal routes.

## Entry Points

- Portal routes: `tickets/*`
- Staff routes: `staff/tickets/*`
- API routes: `api/ticketing/*`
- Internal workflows: resolve container contracts such as `CreatesTickets`,
  `AddsTicketReplies`, `AssignsTickets`, `ProcessesInboundTicketMail`, and
  `PublishesTicketMetrics`.

## Testing

```bash
composer test
composer test:unit
composer test:feature
composer test:integration
```

The package test suite uses PHPUnit and Orchestra Testbench.
