# Contract: Extension API

## Overview

The package exposes explicit extension seams so host applications can customize
behavior without editing package internals.

## Service Provider and Install Surface

- Auto-discovered service provider registers routes, config, bindings, policies,
  events, notifications, and commands.
- Install command or documented setup flow publishes config/migrations and validates
  host integration prerequisites.

## Configurable Model Map

Host applications can override configurable models where practical through config and
container bindings.

**Expected model categories**
- ticket
- conversation entry
- attachment
- assignment
- queue
- team
- watcher
- SLA policy
- automation rule
- tag
- custom field definition/value
- saved view
- audit record
- email thread

## Core Contracts

### Auth & Actor Resolution

- `ResolvesTicketActor`
- `MapsTicketRoles`

### Ticket Lifecycle

- `CreatesTickets`
- `UpdatesTickets`
- `AddsTicketReplies`
- `AssignsTickets`
- `AttachmentStorage`
- `ResolvesAttachmentStorage`

### Automation & SLA

- `EvaluatesAutomationRules`
- `ExecutesAutomationActions`
- `ResolvesSLAPolicies`
- `ComputesSLADeadlines`

### Notifications & Mail

- `ResolvesNotificationRecipients`
- `ProcessesInboundTicketMail`
- `BuildsOutboundTicketMail`

### Search & Reporting

- `SearchesTickets`
- `BuildsSavedViews`
- `PublishesTicketMetrics`

### Multi-tenancy

- `ResolvesTenantContext`

### Implemented Adapters

- Portal routes under the configured `routes.portal.prefix`
- Staff routes under the configured `routes.staff.prefix`
- API routes under the configured `routes.api.prefix`
- Staff Blade view stub published with `ticketing-views`
- Translation strings published with `ticketing-lang`

## Event Surface

The package currently dispatches explicit events for:

- ticket created
- ticket metrics published

Audit records are persisted for:

- ticket created
- public reply added
- internal note added
- ticket assigned
- ticket resolved

Planned additive events include ticket updated, merged, reopened, closed, attachment
added or removed, watcher changed or mentioned, automation executed, SLA warning or
breach, notification dispatched, inbound email processed, and custom field definition
changed.

## Public Route Names

- `ticketing.portal.tickets.index`
- `ticketing.portal.tickets.store`
- `ticketing.portal.tickets.show`
- `ticketing.portal.tickets.replies.store`
- `ticketing.staff.tickets.index`
- `ticketing.staff.tickets.store`
- `ticketing.staff.tickets.notes.store`
- `ticketing.staff.tickets.assignments.store`
- `ticketing.staff.tickets.resolve`
- `ticketing.api.tickets.index`
- `ticketing.api.tickets.store`
- `ticketing.api.tickets.show`
- `ticketing.api.tickets.replies.store`
- `ticketing.api.tickets.assignments.store`
- `ticketing.api.tickets.status-transitions.store`
- `ticketing.api.metadata.index`

## Public Config Keys

- `models.*`
- `features.portal`, `features.staff`, `features.api`, `features.mail`,
  `features.notifications`, `features.broadcasting`, `features.queues`,
  `features.attachments`, `features.multi_tenancy`
- `routes.portal.*`, `routes.staff.*`, `routes.api.*`
- `attachments.disk`, `attachments.directory`, `attachments.max_upload_size_kb`,
  `attachments.visibility`, `attachments.signed_urls`
- `auth.guard`, `auth.user_model`, `auth.morph_name`
- `tenancy.enabled`, `tenancy.resolver`, `tenancy.column`
- `mail.inbound_enabled`, `mail.default_mailbox`, `mail.mailbox`, `mail.from.*`,
  `mail.quarantine_unmatched`
- `queue.connection`, `queue.queue`

## Override Rules

- Host applications may replace contract implementations through container bindings.
- Host applications may subscribe to events without coupling to internal classes beyond
  documented event payloads.
- Host applications may extend notification routing, search providers, reporting
  projectors, and UI adapters via config and service provider hooks.
- Public contracts, config keys, event names, and route names are treated as versioned
  package API.
