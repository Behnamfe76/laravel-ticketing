# Extensibility

Laravel Ticketing exposes its public customization surface through config keys,
container contracts, events, policies, routes, and publishable resources. Host
applications should extend these seams instead of modifying package internals.

## Container Contracts

Auth and actors:

- `Fereydooni\LaravelTicketing\Contracts\Auth\MapsTicketRoles`
- `Fereydooni\LaravelTicketing\Contracts\Auth\ResolvesTicketActor`

Ticket lifecycle:

- `CreatesTickets`
- `UpdatesTickets`
- `AddsTicketReplies`
- `AssignsTickets`
- `AttachmentStorage`
- `ResolvesAttachmentStorage`

Workflow and automation:

- `SearchesTickets`
- `BuildsSavedViews`
- `ComputesSLADeadlines`
- `EvaluatesAutomationRules`
- `ExecutesAutomationActions`
- `ResolvesSLAPolicies`

Mail and notifications:

- `ResolvesNotificationRecipients`
- `ProcessesInboundTicketMail`
- `BuildsOutboundTicketMail`

Reporting and tenancy:

- `PublishesTicketMetrics`
- `ResolvesTenantContext`

Replace implementations in a host service provider:

```php
$this->app->bind(MapsTicketRoles::class, App\Support\TicketRoleMapper::class);
```

## Events

The implemented event surface currently includes:

- `TicketCreated`
- `TicketMetricsPublished`

Audit records are persisted for ticket creation, replies, internal notes, assignments,
and resolution flows. Additional lifecycle events should be additive within the same
major version.

## Routes

Route groups are configured in `config/ticketing.php`:

- `routes.portal`
- `routes.staff`
- `routes.api`

Disable a group by setting `enabled` to `false`, or change its prefix and middleware to
match the host app.

## Publishable Resources

- `ticketing-config`: package config and permissions config
- `ticketing-migrations`: database migrations
- `ticketing-lang`: translations
- `ticketing-views`: optional staff view stubs

## Model Map

The `models` config lists all package model classes used as public model categories,
including tickets, conversation entries, attachments, assignments, queues, teams,
watchers, audit records, custom fields, SLA policies, automation rules, saved views,
tags, and email threads.

## Versioning Discipline

Public contracts, route names, config keys, event names, migration table names, and
resource payload semantics are treated as package API. Additive changes are acceptable
within a major version; renames, removals, or incompatible behavior changes require a
major-version upgrade note.
