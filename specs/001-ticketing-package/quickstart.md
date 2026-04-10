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
```

If the package provides an install command, prefer:

```bash
php artisan ticketing:install
```

## 3. Run migrations

```bash
php artisan migrate
```

## 4. Configure host integration

- Map the host application's authenticatable user model and any staff/agent role
  resolution hooks in `config/ticketing.php`.
- Enable only the modules needed initially, for example:
  ticket creation, staff replies, notifications, and basic assignment.
- Configure queue, mail, and broadcast drivers if asynchronous workflows are desired.
- Configure tenant resolution if the host app is multi-tenant.

## 5. Enable entry points incrementally

Choose one or more of the following:

- internal workflow ticket creation through package actions/services
- REST API routes for ticket operations
- staff-facing routes/controllers
- customer portal routes/controllers
- inbound/outbound email workflows

## 6. Seed baseline metadata

Create initial statuses, priorities, categories, queues, and SLA policies through
package config, seeders, or admin tooling.

## 7. Verify core flow

1. Create a ticket through the chosen entry point.
2. Add a public reply and an internal note.
3. Assign the ticket to a queue or staff user.
4. Confirm notifications and audit records were created.
5. Resolve and reopen the ticket to confirm workflow enforcement.

## 8. Verify package integration in tests

```bash
./vendor/bin/pest
```

or

```bash
./vendor/bin/phpunit
```

Key integration checks:

- package auto-discovery boots successfully
- published config and migrations load in a host app
- host auth model compatibility works without package-owned user assumptions
- queue, mail, and notification flows degrade safely when selectively enabled
