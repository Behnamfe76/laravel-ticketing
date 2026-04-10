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
- queue
- team
- custom field definition/value
- saved view
- audit record

## Core Contracts

### Auth & Actor Resolution

- `ResolvesTicketActor`
- `ResolvesAssignableEntities`
- `MapsTicketRoles`

### Ticket Lifecycle

- `CreatesTickets`
- `UpdatesTickets`
- `AddsTicketReplies`
- `TransitionsTicketStatus`
- `AssignsTickets`

### Automation & SLA

- `EvaluatesAutomationRules`
- `ExecutesAutomationActions`
- `ResolvesSLAPolicies`
- `ComputesSLADeadlines`

### Notifications & Mail

- `ResolvesNotificationRecipients`
- `BuildsTicketNotifications`
- `ProcessesInboundTicketMail`
- `BuildsOutboundTicketMail`

### Search & Reporting

- `SearchesTickets`
- `BuildsSavedViews`
- `PublishesTicketMetrics`

### Multi-tenancy

- `ResolvesTenantContext`
- `AppliesTenantScope`
- `PropagatesTenantContextToJobs`

## Event Surface

The package dispatches explicit events for:

- ticket created, updated, merged, resolved, reopened, closed
- reply added, internal note added
- attachment added or removed
- assignment changed
- watcher added, removed, or mentioned
- automation executed
- SLA warning and SLA breach
- notification dispatched
- inbound email processed
- custom field definition changed

## Override Rules

- Host applications may replace contract implementations through container bindings.
- Host applications may subscribe to events without coupling to internal classes beyond
  documented event payloads.
- Host applications may extend notification routing, search providers, reporting
  projectors, and UI adapters via config and service provider hooks.
- Public contracts, config keys, event names, and route names are treated as versioned
  package API.
