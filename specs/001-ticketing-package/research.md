# Research: Laravel Ticketing Package

## Decision: Package-first Laravel architecture with root package + optional workbench app

**Rationale**: The package must install into existing Laravel applications with minimal
friction while still supporting realistic integration testing and optional UI adapters.
A root package structure keeps Composer packaging straightforward, and a workbench app
supports Testbench-driven package integration without turning the project into a
standalone application.

**Alternatives considered**:
- Standalone Laravel application: rejected because it violates the package adoption goal.
- Monorepo split between package and demo app from day one: rejected because it adds
  packaging complexity before the package surface is stable.

## Decision: Eloquent models with configurable model mapping and morph-friendly relationships

**Rationale**: Eloquent fits Laravel package conventions and supports host application
expectations, factories, policies, scopes, notifications, and queue serialization.
Configurable model bindings let host apps swap key models where practical, while morph
relationships avoid assumptions about a concrete user model implementation.

**Alternatives considered**:
- Doctrine or custom persistence layer: rejected because it increases integration
  friction and diverges from Laravel-native expectations.
- Hard-coding the package to `App\Models\User`: rejected because it breaks host app
  compatibility and multi-auth flexibility.

## Decision: Action/service layer plus repository and query contracts

**Rationale**: Ticketing spans controllers, mail ingestion, jobs, automation, and UI
adapters. Action classes encapsulate commands, while repositories/query services handle
read concerns, keeping domain logic out of controllers and views. Contracts preserve
extension seams and testability.

**Alternatives considered**:
- Fat Eloquent models only: rejected because lifecycle, automation, and email workflows
  would become difficult to reason about and customize.
- Controller-driven orchestration: rejected because the package must support API, portal,
  staff, mail, and job entry points consistently.

## Decision: Policy-driven authorization with role mapping hooks

**Rationale**: Laravel policies and gates are the least surprising integration point for
host applications. A role/ability resolver contract allows host apps to align package
permissions with their own auth stack without forcing a package-owned RBAC model.

**Alternatives considered**:
- Package-specific hard-coded roles only: rejected because host apps vary widely.
- Gate closures scattered across controllers/jobs: rejected because it obscures package
  boundaries and reduces auditability.

## Decision: Queue-first handling for notifications, SLA checks, automation, and mail sync

**Rationale**: Ticketing often involves expensive side effects and time-based workflows.
Queue-first design improves responsiveness and supports incremental adoption since host
applications can enable queue processing as needed.

**Alternatives considered**:
- Synchronous side effects everywhere: rejected because it harms request latency and
  makes inbound/outbound email and notification bursts fragile.

## Decision: REST-style API layer plus optional portal/staff adapters

**Rationale**: The package must support host apps and external systems. A REST-style API
surface provides a stable integration point, while optional portal and staff adapters
allow incremental UI adoption without making UI a hard dependency.

**Alternatives considered**:
- UI-first package with no stable API contract: rejected because external integration is
  a core requirement.
- API-only package with no UI adapters: rejected because staff/customer experiences are
  part of the product value and should be supported, even if optional.

## Decision: Search and reporting as extensible hooks over Eloquent defaults

**Rationale**: Many installations can start with Eloquent query composition, tags,
filters, and saved views. Advanced installations may need dedicated search engines or BI
pipelines later. Contracts and events make this upgrade path predictable.

**Alternatives considered**:
- Require an external search engine from first release: rejected because it increases
  installation friction.
- Treat reporting as package-owned dashboards only: rejected because host applications
  vary in analytics needs.

## Decision: Multi-tenancy compatibility through tenant context contracts rather than bundled tenancy

**Rationale**: Host apps may use different tenancy strategies. A tenant resolver and
context propagation contracts let the package participate in tenant isolation without
forcing a specific tenancy package.

**Alternatives considered**:
- Ship a package-owned tenancy implementation: rejected because it would conflict with
  existing host app architectures.
- Ignore tenancy: rejected because the feature specification requires multi-tenant
  friendliness and predictable isolation.
