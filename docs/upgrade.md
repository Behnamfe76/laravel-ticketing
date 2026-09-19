# Upgrade Guide

## Upgrading from 1.x to 2.0

2.0 closes authorization gaps and makes the package safe to install into an application that
brings its own HTTP layer, migrations, notification pipeline, or tenancy. There are no schema
changes.

### Authorization (breaking)

- `ConfigRoleMapper` now denies by default. Without a host-granted ability, an actor may only
  create tickets and view or reply to tickets they take part in (requester, creator, or
  watcher). In 1.x any authenticated actor could view and reply to every ticket.
- The participant abilities come from `ticketing-permissions.roles.requester`.
- New ability `ticket.view_any`, added to the `agent` and `administrator` roles. It is required
  for the staff index (`GET staff/tickets`), and it is what lets a search return tickets the
  actor does not take part in.
- `SearchesTickets::search()` now honours `$actor`: holders of `ticket.view_any` see every
  ticket, other actors see only their own. Passing `null` marks a trusted system caller and
  is not restricted. `GET api/ticketing/tickets` therefore lists only the caller's tickets
  unless they hold `ticket.view_any`.
- New policy method `TicketPolicy::viewAny()`.

**Action:** grant `ticket.view_any` (and the other staff abilities) to your staff through
`hasTicketingAbility()` on your user model or a custom `MapsTicketRoles` binding.

### Routes (breaking)

`features.portal`, `features.staff`, and `features.api` now default to `false`, and a route
group is registered only when its feature switch **and** `routes.<group>.enabled` are both
true. In 1.x the three groups were registered on every install.

**Action:** set the feature switches you rely on to `true` in `config/ticketing.php`.

### Notifications (breaking)

- `ResolvesNotificationRecipients` gains `forReplyAdded(Ticket, ConversationEntry)`. Custom
  implementations must add it.
- Public replies now send `TicketReplyAddedNotification`; 1.x sent `TicketCreatedNotification`
  for replies.
- The default resolver now includes the requester, sends to each recipient once, and never
  notifies the actor who created the ticket or wrote the reply.
- The built-in listener is subscribed only while `features.notifications` is `true`. Set it to
  `false` to deliver notifications yourself from the package events.
- Channels come from `notifications.channels` (default `['database', 'mail']`). In 1.x the
  `mail` channel was controlled by `features.notifications`.

### Events

- New: `TicketReplyAdded`, `TicketAssigned`, `TicketResolved`, `TicketReopened`.
- All lifecycle events, including `TicketCreated`, implement `ShouldDispatchAfterCommit`, so
  listeners never observe a ticket whose transaction rolled back.
- Every lifecycle event carries an optional `$actor`. `TicketCreated` takes it as a new third
  constructor argument after `$context`.
- `AddReplyAction` no longer calls `DispatchTicketNotifications` directly; it dispatches
  `TicketReplyAdded`.

### Status transitions

New contract `TransitionsTickets` (default `TransitionTicketAction`) with `resolve()` and
`reopen()`. The staff and API transition endpoints use it, so both now write an audit record
(`ticket.resolved`, and the new `ticket.reopened`) and dispatch the matching event. In 1.x the
API transition endpoint wrote no audit record.

### Tenancy

- When `tenancy.enabled` (or the legacy `features.multi_tenancy`) is `true`, every package model
  is scoped to the current tenant by a global scope and stamped with it on create. This covers
  route model binding, relations, the default status lookup, and search. In 1.x only
  `TenantScopedTicketRepository` filtered by tenant, and nothing used it.
- A `null` tenant context matches only rows whose tenant column is `null`.
- `tenancy.resolver` is now honoured: set it to a `ResolvesTenantContext` class to supply the
  tenant id from your own tenancy layer.

### Migrations

New `migrations.load` switch (default `true`). Set it to `false` when you publish the
migrations and run them from your own path(s), for example once per tenant database.

### Actor types

Actors are stored and matched by `getMorphClass()` everywhere, so hosts with a morph map see
their own tickets. Non-Eloquent `Authenticatable` actors are stored by class name instead of
causing a fatal error.

## Release Policy

Releases follow the policy below.

## Public API Rules

Treat the following as versioned public API:

- Container contracts in `src/Contracts`
- Config keys in `config/ticketing.php` and `config/ticketing-permissions.php`
- Route names and route payload semantics
- Event class names and payload properties
- Database table and column names from package migrations
- Publish tags and install command behavior

## Patch Releases

Patch releases may include bug fixes, additional tests, internal refactors, and
documentation improvements. They must not require host application code changes.

## Minor Releases

Minor releases may add optional config keys, routes, contracts, events, fields,
notifications, or resources. Defaults must preserve existing behavior.

## Major Releases

Major releases are required for removed contracts, renamed config keys, incompatible
route payloads, migration-breaking schema changes, or changed authorization semantics.
Every major release should include:

- Changed public APIs
- Required config changes
- Required migration steps
- Backward-incompatible behavior notes
- Test guidance for host applications

## Host Application Checklist

Before upgrading:

1. Run the package and host application test suites.
2. Check custom bindings for contracts listed in `docs/extensibility.md`.
3. Review published config for new keys.
4. Run package migrations in a staging environment.
5. Verify portal, staff, API, email, queue, and reporting flows that are enabled in the
   host application.
