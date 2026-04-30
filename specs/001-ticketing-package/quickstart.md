# Quickstart: Laravel Ticketing Package

## Goal

Install the package into an existing Laravel application and enable an incremental
ticketing workflow without replacing the host application's auth model or UI stack.

## 1. Install the package

```bash
composer require vendor/laravel-ticketing
```

## 2. Publish package assets

```bash
php artisan vendor:publish --tag=ticketing-config
php artisan vendor:publish --tag=ticketing-migrations
php artisan vendor:publish --tag=ticketing-lang
php artisan vendor:publish --tag=ticketing-views
```

If the package provides an install command, prefer:

```bash
php artisan ticketing:install
```

Use `--migrate` to publish and run migrations in one step:

```bash
php artisan ticketing:install --migrate
```

## 3. Run migrations

```bash
php artisan migrate
```

## 4. Configure host integration

- Map the host application's authenticatable user model and any staff/agent role
  resolution hooks in `config/ticketing.php`.
- Map host authorization by replacing the `MapsTicketRoles` binding or by adding a
  `hasTicketingAbility(string $ability, ?Ticket $ticket = null)` method to actor models.
- Enable only the modules needed initially, for example:
  ticket creation, staff replies, notifications, and basic assignment.
- Configure queue, mail, and broadcast drivers if asynchronous workflows are desired.
- Configure tenant resolution if the host app is multi-tenant.
- Configure attachment disk, directory, and visibility for uploaded metadata.
- Configure inbound mailbox defaults and outbound sender settings when mail workflows
  are enabled.

## 5. Enable entry points incrementally

Choose one or more of the following:

- internal workflow ticket creation through package actions/services
- REST API routes for ticket operations
- staff-facing routes/controllers
- customer portal routes/controllers
- inbound/outbound email workflows
- reporting metrics through the `PublishesTicketMetrics` contract

## 6. Seed baseline metadata

Create initial statuses, priorities, categories, queues, and SLA policies through
package config, seeders, or admin tooling.

The package exposes `SyncWorkflowConfigurationAction` for programmatic metadata sync,
including statuses, priorities, categories, queues, tags, SLA policies, saved views, and
custom fields.

## 7. Verify core flow

1. Create a ticket through the chosen entry point.
2. Add a public reply and an internal note.
3. Assign the ticket to a queue or staff user.
4. Confirm notifications and audit records were created.
5. Resolve and reopen the ticket to confirm workflow enforcement.
6. Search tickets by status, tag, queue, or saved view if workflow configuration is
   enabled.
7. Process an inbound email with `ProcessesInboundTicketMail` if mail is enabled.
8. Publish metrics with `PublishesTicketMetrics` if reporting hooks are enabled.

## 8. Verify package integration in tests

```bash
./vendor/bin/pest
```

or

```bash
./vendor/bin/phpunit
```

or:

```bash
composer test
```

Key integration checks:

- package auto-discovery boots successfully
- published config and migrations load in a host app
- host auth model compatibility works without package-owned user assumptions
- queue, mail, and notification flows degrade safely when selectively enabled
- API routes create, reply, assign, transition, and expose metadata
- portal and staff adapters return ticket resources
- reporting and email hooks dispatch stable payloads
