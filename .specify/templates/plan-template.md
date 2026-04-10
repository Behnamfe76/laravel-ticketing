# Implementation Plan: [FEATURE]

**Branch**: `[###-feature-name]` | **Date**: [DATE] | **Spec**: [link]
**Input**: Feature specification from `/specs/[###-feature-name]/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See
`.specify/templates/plan-template.md` for the execution workflow.

## Summary

[Extract from feature spec: primary requirement + technical approach from research]

## Technical Context

<!--
  ACTION REQUIRED: Replace the content in this section with the technical details
  for the project. The structure here is presented in advisory capacity to guide
  the iteration process.
-->

**Language/Version**: [e.g., PHP 8.3 or NEEDS CLARIFICATION]  
**Primary Dependencies**: [e.g., Laravel 11, Orchestra Testbench, Pest/PHPUnit or NEEDS CLARIFICATION]  
**Storage**: [e.g., MySQL/PostgreSQL/SQLite, package migrations, or N/A]  
**Testing**: [e.g., Pest/PHPUnit with unit, feature, and integration suites or NEEDS CLARIFICATION]  
**Target Platform**: [e.g., existing Laravel applications on supported PHP runtimes or NEEDS CLARIFICATION]  
**Project Type**: [Laravel package/library or NEEDS CLARIFICATION]  
**Performance Goals**: [domain-specific, e.g., <200ms p95 for primary workflows or NEEDS CLARIFICATION]  
**Constraints**: [domain-specific, e.g., zero destructive install steps, queue-safe notifications, BC guarantees]  
**Scale/Scope**: [domain-specific, e.g., multi-tenant SaaS installs, large event catalogs, or NEEDS CLARIFICATION]  
**Compatibility Matrix**: [supported Laravel versions, supported PHP versions, database drivers, queue/mail requirements]  
**Public Surface Area**: [configs, contracts, events, commands, migrations, routes, policies, notifications]

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- Laravel-native package integration is preserved: installation path, service provider
  behavior, publishing, routes, commands, migrations, and config remain familiar to
  Laravel developers.
- Supported PHP and Laravel versions are explicitly stated and the planned dependency
  constraints match that support policy.
- Package boundaries are explicit: contracts, actions, models, policies,
  notifications, listeners, jobs, and repositories are identified or intentionally
  omitted.
- Extension seams are documented: config options, events, contracts, publishable
  assets, hooks, or overridable classes affected by this feature are listed.
- Database schema, authorization, notifications, and auditability impact are each
  marked as `N/A` or described with implementation and test implications.
- Unit, feature, and integration tests required for this feature are identified before
  implementation begins.
- Public API changes, deprecations, and migration needs are called out explicitly and
  mapped to semantic versioning impact.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)
<!--
  ACTION REQUIRED: Replace the placeholder tree below with the concrete layout
  for this feature. Delete unused options and expand the chosen structure with
  real paths. The delivered plan must not include Option labels.
-->

```text
# [REMOVE IF UNUSED] Option 1: Laravel package (DEFAULT)
src/
├── Actions/
├── Contracts/
├── Events/
├── Jobs/
├── Listeners/
├── Models/
├── Notifications/
├── Policies/
└── Repositories/

config/
database/
├── factories/
├── migrations/
└── seeders/

routes/

tests/
├── Feature/
├── Integration/
└── Unit/

# [REMOVE IF UNUSED] Option 2: Split package + demo app
package/
└── [same package structure as above]

workbench/ or example-app/
├── app/
├── config/
└── tests/
```

**Structure Decision**: [Document the selected structure and reference the real
directories captured above]

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., additional abstraction layer] | [current need] | [why simpler Laravel-native approach is insufficient] |
| [e.g., custom repository contract] | [specific package boundary] | [why direct model access is insufficient] |
