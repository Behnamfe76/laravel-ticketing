<!--
Sync Impact Report
- Version change: template -> 1.0.0
- Modified principles:
  - Template principle 1 -> I. Laravel-Native Package Integration
  - Template principle 2 -> II. Modern Platform Compatibility
  - Template principle 3 -> III. Extensible Domain Boundaries
  - Template principle 4 -> IV. Layered Test Coverage & Operational Quality
  - Template principle 5 -> V. Stable APIs, Explicit Versioning & Complete Documentation
- Added sections:
  - Package Architecture Standards
  - Delivery Workflow & Release Discipline
- Removed sections:
  - None
- Templates requiring updates:
  - ✅ updated .specify/templates/plan-template.md
  - ✅ updated .specify/templates/spec-template.md
  - ✅ updated .specify/templates/tasks-template.md
- Follow-up TODOs:
  - None
-->
# Laravel Ticketing Constitution

## Core Principles

### I. Laravel-Native Package Integration
This package MUST install into an existing Laravel application with minimal friction.
Service providers, config publishing, migrations, commands, routes, events, and
testing helpers MUST follow Laravel package conventions familiar to Laravel
developers. Package defaults MUST be safe, documented, and usable without requiring
consumer applications to rewrite their own architecture.

Rationale: adoption succeeds when the package behaves like a first-class Laravel
package rather than a standalone application hidden inside `vendor/`.

### II. Modern Platform Compatibility
The package MUST target actively supported Laravel and PHP versions and MUST define
its compatibility policy in package metadata and release notes. New work MUST be
designed for forward maintenance, including framework upgrade paths, dependency
constraints, and deprecation handling. Compatibility decisions MUST favor predictable
upgrades over short-term convenience.

Rationale: production packages fail when support windows are vague or upgrades become
surprising.

### III. Extensible Domain Boundaries
The package MUST prefer extension points over hard-coded business assumptions.
Contracts, actions, models, policies, notifications, listeners, jobs, and
repositories MUST be explicit when they exist and MUST have clear ownership at
package boundaries. Public extension seams such as config options, contracts, events,
publishable resources, and overridable classes MUST be intentionally designed and
documented.

Rationale: package consumers need to adapt behavior without forking the package or
patching internals.

### IV. Layered Test Coverage & Operational Quality
Every change MUST include automated verification at the appropriate layers, with unit,
feature, and integration tests treated as baseline quality gates for production
behavior. Database schema design, authorization, notifications, and auditability MUST
be addressed explicitly in specs, implementation, and tests whenever a feature touches
those concerns. Regressions in these areas block release.

Rationale: these concerns define whether a Laravel package is trustworthy in real
applications, not merely whether code paths execute.

### V. Stable APIs, Explicit Versioning & Complete Documentation
Public APIs MUST remain backward compatible within a major version unless an
intentional breaking change is released under explicit semantic versioning. Every
extension point, config option, event, and public contract MUST be documented before
release. Deprecations MUST be announced in documentation and release notes with a
migration path.

Rationale: maintainability and predictable upgrades depend on consumers knowing what is
stable, what is changing, and how to adapt safely.

## Package Architecture Standards

The package architecture MUST keep boundaries explicit and reviewable.

- Contracts MUST define integration seams exposed to consumers or internal modules.
- Actions, jobs, listeners, notifications, policies, repositories, and models MUST
  have single, named responsibilities and MUST not collapse unrelated concerns into
  convenience classes.
- Database migrations, schema defaults, and persistence rules MUST support safe
  installation into existing applications and MUST avoid destructive assumptions about
  consumer data.
- Authorization rules MUST be policy- or contract-driven rather than embedded as
  hidden conditionals across controllers, listeners, or jobs.
- Notifications and audit records MUST be traceable to package events or explicit
  actions so behavior remains observable and testable.

## Delivery Workflow & Release Discipline

All plans, specs, tasks, reviews, and releases MUST demonstrate constitutional
compliance before implementation is considered complete.

- Feature specs MUST declare affected extension points, configuration, compatibility
  expectations, and whether schema, authorization, notifications, or auditability are
  in scope.
- Implementation plans MUST include a constitution check covering Laravel package
  conventions, compatibility targets, boundary clarity, test coverage, and API
  stability.
- Task lists MUST include documentation work and the required unit, feature, and
  integration tests for each user story or cross-cutting concern.
- Reviews MUST reject undocumented extension surfaces, implicit boundaries, unversioned
  breaking changes, and gaps in operational-quality coverage.
- Releases MUST summarize compatibility, deprecations, migration steps, and any public
  API changes.

## Governance

This constitution supersedes informal local practice for this repository. Amendments
require a documented update to this file, alignment of affected Spec Kit templates, and
an explanation of whether the change is major, minor, or patch in semantic versioning
terms for the constitution itself.

Compliance review is mandatory for every spec, plan, task list, and release. Reviewers
MUST verify:

- installation remains Laravel-native and low-friction,
- supported Laravel and PHP versions remain explicit,
- extension seams and package boundaries remain intentional and documented,
- unit, feature, and integration coverage are present at the appropriate depth,
- schema, authorization, notifications, and auditability are treated as first-class
  concerns where relevant,
- public API changes are versioned and documented before release.

Constitution versioning follows semantic versioning:

- MAJOR for removed principles or materially incompatible governance changes,
- MINOR for new principles, new mandatory sections, or materially stronger guidance,
- PATCH for clarifications that do not change enforcement intent.

**Version**: 1.0.0 | **Ratified**: 2026-04-10 | **Last Amended**: 2026-04-10
