# Implementation Plan: Laravel Ticketing Package

**Branch**: `001-ticketing-package` | **Date**: 2026-04-10 | **Spec**: [/Users/fereydooni/Documents/GITHUB/php-packages/laravel-ticketing/specs/001-ticketing-package/spec.md](/Users/fereydooni/Documents/GITHUB/php-packages/laravel-ticketing/specs/001-ticketing-package/spec.md)
**Input**: Feature specification from `/specs/001-ticketing-package/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See
`.specify/templates/plan-template.md` for the execution workflow.

## Summary

Build a reusable Laravel package that adds production-grade support and service desk
ticketing to existing Laravel applications with minimal installation friction. The
architecture centers on explicit domain modules, Eloquent persistence, Laravel-native
service provider integration, REST-style API support, package actions/services,
policy-driven authorization, queue-safe notifications and jobs, and well-defined
contracts so host applications can incrementally adopt or override behavior without
forking package internals.

## Technical Context

**Language/Version**: PHP 8.3+  
**Primary Dependencies**: Laravel package tools, Laravel service container, Eloquent ORM, Pest or PHPUnit, Orchestra Testbench  
**Storage**: Eloquent-backed relational database with publishable package migrations; default compatibility with MySQL, PostgreSQL, and SQLite for testing  
**Testing**: Pest or PHPUnit with Orchestra Testbench; unit, feature, and integration suites required  
**Target Platform**: Existing Laravel applications integrating the package through Composer and service provider auto-discovery  
**Project Type**: Laravel package/library  
**Performance Goals**: Ticket creation, reply submission, assignment updates, and filtered listing remain suitable for interactive application workflows; expensive work is queueable and bulk-safe  
**Constraints**: Zero destructive install steps, Laravel-native package conventions, backward-compatible public API discipline, configurable models where practical, no hard dependency on a concrete host user model, separate domain logic from HTTP/UI adapters  
**Scale/Scope**: Multi-tenant-friendly support desk domain covering tickets, replies, notes, assignments, queues, taxonomy, automation, attachments, notifications, email sync, search, custom fields, reporting hooks, REST APIs, and UI adapters  
**Compatibility Matrix**: PHP 8.3+; modern supported Laravel versions; database support for MySQL/PostgreSQL and SQLite in tests; queue/mail/broadcast integrations optional but first-class  
**Public Surface Area**: Config file, service provider, install/setup command or documented setup flow, publishable migrations/resources, REST API routes, policies, events, notifications, jobs, contracts, model customization points, and extension listeners

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Laravel-native package integration is preserved: Composer package, auto-discovered
  service provider, publishable config/migrations, optional routes/views, and
  install/setup command are part of the design.
- Supported PHP and Laravel versions are explicit: package targets PHP 8.3+ and
  currently supported modern Laravel releases.
- Package boundaries are explicit: module plan includes contracts, actions, models,
  policies, notifications, listeners, jobs, repositories, and API/UI adapters as
  separate concerns.
- Extension seams are documented: model resolution, contracts, events, notification
  routing, automation actions, reporting hooks, authorization integration, tenant
  resolution, email ingestion, and UI adapters are all named extension points.
- Database schema, authorization, notifications, and auditability are first-class:
  dedicated entities, rules, and test coverage exist in the design for each concern.
- Unit, feature, and integration tests are mandated across lifecycle rules, package
  integration, auth compatibility, queued work, and email/API behavior.
- Public API changes are controllable: the package is new, but all REST endpoints,
  contracts, config keys, events, and model customization seams are treated as
  versioned public surface areas from first release.

Post-design gate review: PASS. No constitutional violations require justification.

## Project Structure

### Documentation (this feature)

```text
specs/001-ticketing-package/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── extension-api.md
│   └── rest-api.md
└── tasks.md
```

### Source Code (repository root)

```text
src/
├── Actions/
│   ├── Tickets/
│   ├── Replies/
│   ├── Assignments/
│   ├── Automation/
│   ├── Search/
│   └── Reporting/
├── Console/
│   └── Commands/
├── Contracts/
│   ├── Auth/
│   ├── Automation/
│   ├── Mail/
│   ├── Models/
│   ├── MultiTenancy/
│   ├── Notifications/
│   ├── Reporting/
│   ├── Search/
│   └── Tickets/
├── DTOs/
├── Enums/
├── Events/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   ├── Portal/
│   │   └── Staff/
│   ├── Requests/
│   ├── Resources/
│   └── Middleware/
├── Jobs/
├── Listeners/
├── Mail/
├── Models/
├── Notifications/
├── Policies/
├── Providers/
├── Repositories/
│   ├── Eloquent/
│   └── Search/
├── Rules/
├── Services/
│   ├── Automation/
│   ├── Email/
│   ├── SLA/
│   └── Tenancy/
├── Support/
│   ├── Forms/
│   ├── Mentions/
│   ├── Routing/
│   └── Serialization/
└── UI/
    ├── Portal/
    └── Staff/

config/
├── ticketing.php
└── ticketing-permissions.php

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── lang/
├── views/
└── stubs/

routes/
├── api.php
├── portal.php
└── staff.php

tests/
├── Feature/
├── Integration/
└── Unit/

workbench/
├── app/
├── config/
├── routes/
└── tests/
```

**Structure Decision**: Use a package-at-root structure with an optional `workbench/`
application for package integration testing and UI/API demos. Domain logic lives in
`src/Actions`, `src/Services`, and `src/Repositories`; HTTP and UI entry points are
adapters only. This keeps the package reusable inside existing Laravel apps while
still enabling realistic integration coverage through Testbench and a workbench app.

## Module Architecture

### Tickets

- Core aggregate centered on ticket identity, requester, current assignment, lifecycle
  state, taxonomy, SLA state, tenant scope, and search metadata.
- Actions handle create, update, reclassify, merge, resolve, reopen, and close flows.
- Repository contracts abstract ticket retrieval patterns needed by API, portal, and
  staff experiences.

### Replies and Notes

- Conversation entries are immutable records linked to tickets with visibility
  semantics (`public_reply`, `internal_note`, system-generated message).
- Mention extraction, watcher synchronization, and notification fan-out occur through
  dedicated actions and listeners.

### Assignments and Queues

- Support assignment to users, teams, and queues through a common assignment target
  abstraction.
- Queue membership and routing rules are configuration-driven and tenant-aware.
- Assignment history is auditable and queryable.

### Statuses, Priorities, Categories, Tags

- Taxonomy is configuration-backed but persisted where runtime management is required.
- Status transitions are validated against allowed workflow maps and automation rules.
- Tags and saved views support staff operations and reporting hooks.

### SLA and Escalation Rules

- SLA policies attach to tickets by category, priority, queue, or custom rule inputs.
- A dedicated escalation engine evaluates timers, breach thresholds, reminders, and
  escalation actions through queueable jobs and scheduler-friendly entry points.
- Automation precedence and escalation conflicts are resolved deterministically through
  explicit rule ordering and stop-processing semantics.

### Attachments

- Attachment metadata is persisted in package tables while binary storage is mediated
  through a storage abstraction that resolves Laravel disks, visibility, signed access,
  retention, and cleanup behavior.
- Access checks follow ticket/reply visibility policies.
- Upload validation, disk selection, retrieval authorization, and retention hooks are
  configurable and overridable by contract.

### Notifications

- Domain events trigger notification routing through mail, database, and broadcast
  channels.
- Channel resolution and recipient selection are overridable by contract.
- High-volume notifications are queue-safe by default.

### Email Ingestion and Outbound Mail Sync

- Inbound mail parsing maps messages to new or existing tickets, authors, and
  attachments using dedicated mail ingestion services and jobs.
- Outbound mailables mirror public replies and lifecycle updates while preserving
  message threading metadata.
- Mailbox routing, thread correlation, unmatched-message quarantine, and operational
  observability are explicit parts of the email subsystem.
- Ambiguous inbound messages are quarantined or flagged instead of silently mutating
  tickets.

### Reporting Hooks

- Expose query contracts, reporting events, and projection hooks rather than hard-code
  dashboards into the package.
- Consumers can subscribe to ticket lifecycle events or repository queries to build
  dashboards and exports.

### Custom Fields

- Custom field definitions drive ticket forms, validation, serialization, indexing, and
  API exposure.
- Validation strategy includes field-type validators, conditional visibility rules,
  normalization, persistence-safe serialization, and parity between API, action, and UI
  request handling.
- Field types are extensible, and host apps can register additional renderers, rule
  resolvers, or normalizers.

### Search and Filtering

- Filter DTOs and query services handle saved views, staff filters, portal filtering,
  and API search parameters.
- Default implementation uses Eloquent query composition with a contract seam for
  advanced search engines later.

### Events, Contracts, and Extension API

- Every major lifecycle action emits explicit domain events.
- Contracts define model resolution, auth integration, tenancy resolution, search,
  reporting, mail ingestion, notification routing, and automation action execution.
- Host apps can swap implementations via container bindings and config-driven model
  maps.

### Admin/Staff UI Adapters

- Package may ship optional Blade/inertia-agnostic adapters or controllers/resources
  that host apps can enable selectively.
- Staff UI adapters depend on actions/services and policies, not direct model logic.
- Minimal staff-facing entry points for the core lifecycle are part of the MVP package
  surface; richer scaffolding remains optional.

### Public/Customer Portal Support

- Separate portal routes, policies, request validators, and resources handle requester
  experiences.
- Portal features are installable independently from staff tooling so existing apps can
  adopt incrementally.
- Minimal customer-facing entry points for ticket creation and reply flows are part of
  the MVP package surface; richer scaffolding remains optional.

### Multi-Tenancy Compatibility

- Tenant scoping is resolved through a contract rather than a package-owned tenancy
  implementation.
- Models, repositories, API resources, portal/staff adapters, and saved views accept
  tenant context and apply tenant scopes consistently.
- Queue jobs, notifications, email ingestion, and reporting payloads carry tenant
  context explicitly.
- Tenant resolution, scope enforcement, and cross-tenant safety checks are validated in
  dedicated integration tests.

## Testing Strategy

- Unit tests: lifecycle state machines, automation evaluators, SLA calculations,
  mention parsing, tenant resolution helpers, custom field rules, authorization policy
  decisions, attachment storage resolution, and notification routing decisions.
- Feature tests: ticket creation, portal replies, internal notes, assignment changes,
  search/filter endpoints, saved views, staff-facing access, customer-facing access,
  policies, upload flows, and install command behavior.
- Integration tests: Composer package boot, auto-discovery, published migrations/config,
  host auth compatibility, queue processing, mail ingestion, broadcast/database/mail
  notifications, attachment storage integration, mailbox routing, and multi-tenant
  isolation with Testbench/workbench.

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Custom repository and query contracts | Search, reporting hooks, tenancy scoping, and API reuse need stable extension seams | Direct model queries across controllers and UI adapters would entangle package boundaries and block customization |
| Optional workbench application | Realistic package integration, route/UI adapter verification, and install-flow testing need a host app context | Pure unit/feature tests without a host-like environment would miss adoption and bootstrapping regressions |
