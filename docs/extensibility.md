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
- `TransitionsTickets`
- `ManagesWatchers`
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

- `TicketCreated`: `$ticket`, `$context`, `$actor`
- `TicketReplyAdded`: `$ticket`, `$entry`, `$actor` (public replies and internal notes; check
  `$entry->entry_type`)
- `TicketAssigned`: `$ticket`, `$assignment`, `$actor`
- `TicketResolved`: `$ticket`, `$actor`
- `TicketReopened`: `$ticket`, `$actor`
- `TicketStatusChanged`: `$ticket`, `$fromStatusId`, `$toStatusId`, `$actor`
- `TicketUpdated`: `$ticket`, `$changes`, `$actor`
- `TicketWatcherAdded`: `$ticket`, `$watcher`, `$actor`
- `TicketWatcherRemoved`: `$ticket`, `$watcherType`, `$watcherId`, `$relationType`, `$actor`
- `TicketMetricsPublished`

The lifecycle events implement `ShouldDispatchAfterCommit`. `$actor` is `null` for system
actions.

Audit records are persisted for ticket creation, updates, replies, internal notes,
assignments, status changes, resolution, reopening, and watcher changes. Additional lifecycle events should be additive within the same
major version.

## Notifications

While `features.notifications` is `true`, the package subscribes `DispatchTicketNotifications`,
which sends `TicketCreatedNotification` and `TicketReplyAddedNotification` over
`notifications.channels` to the recipients returned by `ResolvesNotificationRecipients`.

To use your own notification pipeline, set `features.notifications` to `false` and listen to
the lifecycle events above.

## Authorization

`MapsTicketRoles` decides every ability. The default `ConfigRoleMapper` grants an ability when
the actor's `hasTicketingAbility($ability, $ticket)` returns true, or when the ability is in
`ticketing-permissions.roles.requester` and the actor takes part in the ticket (requester,
creator, or watcher). Everything else is denied.

`ticket.view_any` lets an actor list and search every ticket in the current tenant.

## Attachments

`AttachmentStorage::store()` stores an `UploadedFile` on `attachments.disk` under
`attachments.directory`, with a generated name, a detected MIME type, and a SHA-256 checksum,
after checking `attachments.max_upload_size_kb`. `attach()` records metadata for a file the
host has already stored and must never receive user input. Downloads go through the adapters'
authorized `tickets/{ticket}/attachments/{attachment}` routes, or `AttachmentManager::download()`
from host code after your own authorization.

## Tenancy

Set `tenancy.enabled` to scope every package model to the current tenant. The tenant id comes
from `ResolvesTenantContext`: by default the package `TenantContext` (set it with
`TenantContext::set()`), or the class named in `tenancy.resolver`.

Hosts that separate tenants by database rather than by column can leave tenancy disabled, set
`migrations.load` to `false`, and publish the migrations into each database's migration path.

## Routes

Route groups are configured in `config/ticketing.php`:

- `routes.portal`
- `routes.staff`
- `routes.api`

A group is registered only when both its `features.<group>` switch and its
`routes.<group>.enabled` flag are `true`. The feature switches default to `false`. Change a
group's prefix and middleware to match the host app, including its guard.

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
