# Testing And Compatibility

## Compatibility Matrix

| Area | Supported |
| --- | --- |
| PHP | 8.3+ |
| Laravel components | 11.x and 12.x |
| Test harness | PHPUnit 11 with Orchestra Testbench 9 or 10 |
| Databases | SQLite in tests; Laravel-supported relational databases for hosts |
| Queues | Laravel queue drivers through configured connection and queue name |
| Mail | Laravel mail and notification components |

## Package Test Commands

```bash
composer test
composer test:unit
composer test:feature
composer test:integration
```

## Coverage Areas

Unit coverage includes lifecycle behavior, authorization mapping, custom field
validation, search filters, SLA deadlines, automation precedence, mail threading,
reporting payloads, and tenant context propagation.

Feature coverage includes portal flows, staff flows, API lifecycle operations,
authorization denials, collaboration behavior, and admin workflow configuration.

Integration coverage includes package bootstrapping, migration loading, host auth model
compatibility, configurable workflow behavior, email processing, reporting hooks, API
adapters, UI adapters, and tenant-aware flows.

## Host Application Testing

Host applications should add tests for their own role mapper, tenant resolver, custom
model overrides, custom notification routing, enabled route middleware, queue driver,
mailbox ingestion pipeline, and any custom reporting subscribers.

## Quick Validation

Run the package suite after dependency installation:

```bash
composer install
composer test
```

For host apps, also run:

```bash
php artisan ticketing:install --force
php artisan migrate
php artisan route:list --name=ticketing
```
