# Tasks: Laravel Ticketing Package

**Input**: Design documents from `/specs/001-ticketing-package/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Every feature MUST include the necessary unit, feature, and integration
tests required by the constitution. Omit a level only when the plan explicitly
justifies why it is not applicable.

**Organization**: Tasks are grouped by user story to enable independent implementation
and testing of each story while also following the requested slice sequence to keep
delivery incremental and non-chaotic.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Laravel package**: `src/`, `config/`, `database/`, `routes/`, `tests/` at repository root
- **Split package + workbench app**: `package/` and `workbench/` or `example-app/`
- Paths shown below assume a Laravel package at repository root; adjust based on
  `plan.md`

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Bootstrap the Composer package and installation experience

- [ ] T001 Create Composer package metadata and package autoload structure in `composer.json`
- [ ] T002 Create package service provider and auto-discovery wiring in `src/Providers/TicketingServiceProvider.php`
- [ ] T003 [P] Create primary package configuration, model map defaults, and feature toggles in `config/ticketing.php`
- [ ] T004 [P] Create package permission and ability mapping configuration in `config/ticketing-permissions.php`
- [ ] T005 [P] Create package install command and publish tags in `src/Console/Commands/InstallTicketingCommand.php`
- [ ] T006 [P] Configure Pest or PHPUnit with Orchestra Testbench bootstrap in `tests/TestCase.php`
- [ ] T007 [P] Create workbench integration bootstrap for package verification in `workbench/bootstrap/app.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core package infrastructure that MUST be complete before ANY user story can be implemented

**CRITICAL**: No user story work can begin until this phase is complete

- [ ] T008 Create base publishable migrations for shared ticketing tables in `database/migrations/2026_04_10_000001_create_ticketing_core_tables.php`
- [ ] T009 [P] Create foundational contracts for actor, tenant, and model resolution in `src/Contracts/Auth/ResolvesTicketActor.php`
- [ ] T010 [P] Create package model base classes and morph-friendly relationships in `src/Models/Ticket.php`
- [ ] T011 [P] Create shared domain events and event payload conventions in `src/Events/TicketCreated.php`
- [ ] T012 [P] Create audit logging infrastructure and immutable audit record model in `src/Models/AuditRecord.php`
- [ ] T013 [P] Create queue-safe tenant context propagation support in `src/Services/Tenancy/TenantContext.php`
- [ ] T014 [P] Create package integration test covering auto-discovery, config publishing, and migrations in `tests/Integration/PackageBootTest.php`
- [ ] T015 [P] Create tenant resolver and scope application contracts in `src/Contracts/MultiTenancy/ResolvesTenantContext.php`
- [ ] T016 [P] Create attachment storage abstraction contracts in `src/Contracts/Tickets/ResolvesAttachmentStorage.php`

**Checkpoint**: Foundation ready; user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - Submit and Resolve Tickets (Priority: P1) MVP

**Goal**: Deliver the core operational ticket lifecycle for host applications

**Independent Test**: A host application can create a ticket from customer-facing and
staff-facing entry points, add replies and internal notes, assign it, authorize access
correctly, receive notifications, attach files through a configurable storage layer, and
resolve or reopen the ticket with full activity history.

### Tests for User Story 1

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [ ] T017 [P] [US1] Create unit tests for ticket lifecycle transitions in `tests/Unit/Tickets/TicketLifecycleTest.php`
- [ ] T018 [P] [US1] Create feature tests for portal and staff ticket submission and resolution flows in `tests/Feature/Tickets/SubmitAndResolveTicketTest.php`
- [ ] T019 [P] [US1] Create integration tests for host-app ticket creation and install flow in `tests/Integration/Tickets/HostTicketLifecycleTest.php`
- [ ] T020 [P] [US1] Create unit tests for ticket, reply, and assignment authorization rules in `tests/Unit/Auth/TicketPolicyTest.php`
- [ ] T021 [P] [US1] Create feature tests for portal/staff access denials and watcher visibility in `tests/Feature/Auth/TicketAuthorizationTest.php`

### Implementation for User Story 1

#### Slice: Package bootstrap and install experience

- [ ] T022 [US1] Implement install command workflow, publish tags, and setup guidance output in `src/Console/Commands/InstallTicketingCommand.php`
- [ ] T023 [US1] Register package routes, commands, events, and bindings in `src/Providers/TicketingServiceProvider.php`

#### Slice: Ticket core domain

- [ ] T024 [P] [US1] Implement ticket domain model, taxonomy relations, and tenant scoping in `src/Models/Ticket.php`
- [ ] T025 [P] [US1] Implement ticket creation and update contracts in `src/Contracts/Tickets/CreatesTickets.php`
- [ ] T026 [US1] Implement ticket create/update actions and service orchestration in `src/Actions/Tickets/CreateTicketAction.php`
- [ ] T027 [US1] Implement ticket persistence and query repository in `src/Repositories/Eloquent/EloquentTicketRepository.php`

#### Slice: Replies, notes, and activity history

- [ ] T028 [P] [US1] Implement conversation entry and audit record models in `src/Models/ConversationEntry.php`
- [ ] T029 [P] [US1] Implement reply and internal-note action contract in `src/Contracts/Tickets/AddsTicketReplies.php`
- [ ] T030 [US1] Implement reply, internal note, and activity history actions in `src/Actions/Replies/AddReplyAction.php`
- [ ] T031 [US1] Implement audit event recording listeners for ticket and reply flows in `src/Listeners/RecordTicketAuditTrail.php`

#### Slice: Assignment and queueing

- [ ] T032 [P] [US1] Implement assignment, queue, and team models in `src/Models/Assignment.php`
- [ ] T033 [P] [US1] Implement assignment contract and assignable-entity resolver in `src/Contracts/Tickets/AssignsTickets.php`
- [ ] T034 [US1] Implement assignment and queue-routing actions in `src/Actions/Assignments/AssignTicketAction.php`

#### Slice: Authorization and policies

- [ ] T035 [P] [US1] Implement ticket role-mapping and actor-resolution contracts in `src/Contracts/Auth/MapsTicketRoles.php`
- [ ] T036 [US1] Implement ticket, reply, and assignment policies in `src/Policies/TicketPolicy.php`
- [ ] T037 [US1] Wire gates and policy registration into the package provider in `src/Providers/TicketingServiceProvider.php`

#### Slice: Notifications and watchers

- [ ] T038 [P] [US1] Implement watcher participant model and notification recipient contract in `src/Models/Watcher.php`
- [ ] T039 [P] [US1] Implement ticket notification classes for mail, database, and broadcast channels in `src/Notifications/TicketCreatedNotification.php`
- [ ] T040 [US1] Implement watcher sync, mention parsing, and notification routing listeners in `src/Listeners/DispatchTicketNotifications.php`

#### Slice: Attachments

- [ ] T041 [P] [US1] Implement attachment model and validation rules in `src/Models/Attachment.php`
- [ ] T042 [P] [US1] Implement attachment storage resolver and disk policy contract in `src/Contracts/Tickets/AttachmentStorage.php`
- [ ] T043 [US1] Implement ticket and reply attachment handling service with configurable storage resolution in `src/Services/Attachments/AttachmentManager.php`
- [ ] T044 [US1] Implement feature and integration coverage for replies, assignments, notifications, audit trail, attachment storage, and retrieval rules in `tests/Feature/Tickets/TicketCollaborationTest.php`

#### Slice: Minimal customer-facing and staff-facing experiences

- [ ] T045 [P] [US1] Implement minimal portal ticket create/reply controllers in `src/Http/Controllers/Portal/PortalTicketController.php`
- [ ] T046 [P] [US1] Implement minimal staff ticket workflow controllers in `src/Http/Controllers/Staff/TicketWorkflowController.php`
- [ ] T047 [US1] Register core portal and staff routes for MVP lifecycle flows in `routes/portal.php`

**Checkpoint**: User Story 1 should now be fully functional and testable independently

---

## Phase 4: User Story 2 - Operate and Customize Ticket Workflows (Priority: P2)

**Goal**: Deliver configurable workflow management, admin customization, and operational controls

**Independent Test**: An administrator can configure statuses, priorities, categories,
queues, SLAs, escalation behavior, custom fields, search views, automation rules, and
tenant-aware workflow boundaries, and those changes affect newly created and existing
tickets without editing package internals.

### Tests for User Story 2

- [ ] T048 [P] [US2] Create unit tests for search filters, SLA calculations, escalation ordering, and automation precedence in `tests/Unit/Workflow/WorkflowRulesTest.php`
- [ ] T049 [P] [US2] Create unit tests for custom field validators and normalization rules in `tests/Unit/Forms/CustomFieldValidationTest.php`
- [ ] T050 [P] [US2] Create feature tests for admin workflow configuration and saved views in `tests/Feature/Admin/AdminWorkflowConfigurationTest.php`
- [ ] T051 [P] [US2] Create integration tests for configurable metadata and tenant-aware behavior in `tests/Integration/Admin/ConfigurableWorkflowIntegrationTest.php`

### Implementation for User Story 2

#### Slice: Search, filtering, tags, saved views

- [ ] T052 [P] [US2] Implement tags and saved-view models in `src/Models/Tag.php`
- [ ] T053 [P] [US2] Implement search and saved-view contracts in `src/Contracts/Search/SearchesTickets.php`
- [ ] T054 [US2] Implement Eloquent ticket filtering and saved-view query services in `src/Repositories/Search/EloquentTicketSearchRepository.php`

#### Slice: SLA, automation, escalation

- [ ] T055 [P] [US2] Implement SLA policy and automation rule models in `src/Models/SLAPolicy.php`
- [ ] T056 [P] [US2] Implement SLA, escalation engine, and automation contracts in `src/Contracts/Automation/ComputesSLADeadlines.php`
- [ ] T057 [US2] Implement SLA calculation, escalation jobs, and automation execution services in `src/Services/SLA/SLADeadlineCalculator.php`
- [ ] T058 [US2] Implement escalation engine scheduling, breach actions, and conflict resolution in `src/Services/SLA/EscalationEngine.php`

#### Slice: Custom fields and forms

- [ ] T059 [P] [US2] Implement custom field definition and value models in `src/Models/CustomFieldDefinition.php`
- [ ] T060 [P] [US2] Implement custom field validator, normalizer, and conditional visibility rules in `src/Support/Forms/CustomFieldValidator.php`
- [ ] T061 [US2] Implement form schema builder and API/action validation parity in `src/Support/Forms/CustomFieldFormBuilder.php`
- [ ] T062 [US2] Implement configurable metadata seeders and admin-facing workflow configuration actions in `src/Actions/Admin/SyncWorkflowConfigurationAction.php`

#### Slice: Authorization and policies extension

- [ ] T063 [US2] Extend policies and permission mapping for queues, metadata management, saved views, and automation administration in `src/Policies/TicketAdministrationPolicy.php`

#### Slice: Multi-tenancy compatibility hardening

- [ ] T064 [P] [US2] Implement tenant-aware repository and saved-view scoping support in `src/Repositories/Eloquent/TenantScopedTicketRepository.php`
- [ ] T065 [US2] Implement tenant scope enforcement across tickets, queues, metadata, and admin actions in `src/Services/Tenancy/TenantScopeManager.php`

**Checkpoint**: User Stories 1 and 2 should both work independently

---

## Phase 5: User Story 3 - Integrate Ticketing with External and Internal Systems (Priority: P3)

**Goal**: Deliver external integration surfaces, optional adapters, and extensibility hooks

**Independent Test**: A host application can drive ticketing through APIs and email,
subscribe to events and reporting hooks, optionally enable richer staff/customer
adapters, and run these flows safely through queues, mailbox routing, and tenant-aware
integrations.

### Tests for User Story 3

- [ ] T066 [P] [US3] Create unit tests for mail threading, mailbox routing, reporting payloads, and tenant propagation in `tests/Unit/Integration/ExternalIntegrationRulesTest.php`
- [ ] T067 [P] [US3] Create feature tests for API, portal, and staff adapter flows in `tests/Feature/Integration/ApiAndUiAdaptersTest.php`
- [ ] T068 [P] [US3] Create integration tests for inbound mail, outbound sync, queues, routing, and extension hooks in `tests/Integration/Integration/EmailApiAndHooksTest.php`

### Implementation for User Story 3

#### Slice: Email integration

- [ ] T069 [P] [US3] Implement inbound and outbound email thread model in `src/Models/EmailThread.php`
- [ ] T070 [P] [US3] Implement email ingestion, mailbox routing, and outbound mail contracts in `src/Contracts/Mail/ProcessesInboundTicketMail.php`
- [ ] T071 [US3] Implement inbound parser, mailbox router, quarantine handling, outbound sync mailables, and queued mail jobs in `src/Services/Email/InboundTicketMailProcessor.php`
- [ ] T072 [US3] Implement mailbox routing configuration and unmatched-message observability in `config/ticketing.php`

#### Slice: Reporting hooks and metrics

- [ ] T073 [P] [US3] Implement reporting hook contracts and projection DTOs in `src/Contracts/Reporting/PublishesTicketMetrics.php`
- [ ] T074 [US3] Implement reporting event dispatch and metrics publishing service in `src/Actions/Reporting/PublishTicketMetricsAction.php`

#### Slice: API layer

- [ ] T075 [P] [US3] Implement API requests and resources for tickets, replies, assignments, metadata, and custom fields in `src/Http/Requests/Api/CreateTicketRequest.php`
- [ ] T076 [P] [US3] Implement REST API controllers for ticket lifecycle endpoints in `src/Http/Controllers/Api/TicketController.php`
- [ ] T077 [US3] Register versioned package API routes and middleware in `routes/api.php`

#### Slice: Optional UI scaffolding

- [ ] T078 [P] [US3] Implement richer staff-facing adapter controllers and route entry points in `src/Http/Controllers/Staff/TicketDashboardController.php`
- [ ] T079 [P] [US3] Implement richer customer-portal adapter controllers and route entry points in `src/Http/Controllers/Portal/PortalTicketController.php`
- [ ] T080 [US3] Add optional Blade view stubs and translation strings for portal and staff adapters in `resources/views/staff/tickets/index.blade.php`

#### Slice: Events, contracts, and extension API hardening

- [ ] T081 [US3] Implement documented extension bindings, event payload stability, and contract registration in `src/Providers/TicketingServiceProvider.php`
- [ ] T082 [US3] Implement end-to-end integration coverage for API, email, reporting hooks, UI adapters, mailbox routing, and tenant-aware queues in `tests/Integration/Integration/EndToEndIntegrationTest.php`

**Checkpoint**: All user stories should now be independently functional

---

## Phase N: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] T083 [P] Add package README installation and incremental adoption guide in `README.md`
- [ ] T084 [P] Add extension-point, config, event, and contract reference documentation in `docs/extensibility.md`
- [ ] T085 [P] Add upgrade and versioning guide for future releases in `docs/upgrade.md`
- [ ] T086 [P] Add compatibility matrix and package testing guidance in `docs/testing.md`
- [ ] T087 Review public API, config keys, event names, route names, and storage contracts for semantic versioning impact in `specs/001-ticketing-package/contracts/extension-api.md`
- [ ] T088 Run quickstart validation and align setup docs with install command, portal/staff MVP flows, and mailbox routing behavior in `specs/001-ticketing-package/quickstart.md`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion; blocks all user stories
- **User Story 1 (Phase 3)**: Depends on Foundational completion; delivers MVP with minimal customer-facing and staff-facing entry points
- **User Story 2 (Phase 4)**: Depends on core domain, taxonomy, policy, and tenant foundations from US1
- **User Story 3 (Phase 5)**: Depends on stable lifecycle events, queue-safe flows, explicit storage and tenancy seams, and extension points from US1 and US2
- **Polish (Final Phase)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Package bootstrap and install experience → ticket core domain → replies/notes/activity → assignment/queueing → authorization/policies → notifications/watchers → attachments → minimal portal/staff entry points
- **User Story 2 (P2)**: Search/filtering builds on ticket and taxonomy persistence; SLA/automation depends on assignments, statuses, and activity events; custom fields/forms depend on ticket create/update flows; tenant hardening spans admin and query surfaces
- **User Story 3 (P3)**: Email integration and API layer depend on stable core actions; reporting hooks depend on events and audit trail; richer UI scaffolding depends on policies and request/resource layers

### Within Each User Story

- Unit, feature, and integration tests MUST be written and FAIL before implementation
- Contracts and models before actions/services where applicable
- Actions/services before controllers, listeners, jobs, or notifications
- Core implementation before documentation sign-off
- Story complete before moving to next priority

### Parallel Opportunities

- Setup tasks marked `[P]` can run in parallel after `T001` and `T002`
- Foundational contracts, model bases, events, storage seams, and tenant support marked `[P]` can run in parallel
- Within US1, ticket models/contracts, replies, assignments, notifications, attachments, and minimal portal/staff controllers contain multiple parallel file groups
- Within US2, search, escalation engine, custom-field validation, and tenant-scoping work can run in parallel before orchestration tasks
- Within US3, email, reporting, API adapters, and richer UI scaffolding can be split across contributors once shared contracts stabilize

---

## Parallel Example: User Story 1

```bash
# Launch core ticket and authorization tests together:
Task: "Create unit tests in tests/Unit/Tickets/TicketLifecycleTest.php"
Task: "Create feature tests in tests/Feature/Tickets/SubmitAndResolveTicketTest.php"
Task: "Create unit tests in tests/Unit/Auth/TicketPolicyTest.php"
Task: "Create feature tests in tests/Feature/Auth/TicketAuthorizationTest.php"

# Launch core domain slices together after foundational setup:
Task: "Implement ticket domain model in src/Models/Ticket.php"
Task: "Implement conversation entry model in src/Models/ConversationEntry.php"
Task: "Implement assignment model in src/Models/Assignment.php"
Task: "Implement watcher model in src/Models/Watcher.php"
Task: "Implement attachment model in src/Models/Attachment.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: User Story 1 in slice order
4. Stop and validate User Story 1 independently
5. Release/demo if ready

### Incremental Delivery

1. Complete package bootstrap and install experience
2. Deliver the full core operational lifecycle and minimal portal/staff entry points in US1
3. Add admin customization, escalation, custom-field validation, and workflow controls in US2
4. Add external integration surfaces, mailbox routing, reporting hooks, and richer adapters in US3
5. Finish with documentation, compatibility guidance, and upgrade notes

### Parallel Team Strategy

1. One contributor can own package bootstrap and provider wiring
2. One contributor can own ticket/reply/assignment/auth domain slices
3. One contributor can own workflow customization, custom fields, and tenancy slices
4. One contributor can own integration surfaces such as API, email, and reporting
5. Documentation and upgrade guidance can proceed in parallel near the end once public surface stabilizes

---

## Notes

- `[P]` tasks = different files, no dependencies
- `[Story]` label maps task to a specific user story for traceability
- Each user story is independently completable and testable
- The requested slice order is preserved inside the story phases to avoid a chaotic giant backlog
- Explicit authorization tests, attachment storage seams, custom-field validation strategy, tenant-scoping work, and mailbox routing are now first-class implementation items
- Verify required tests fail before implementing
- Include documentation for every config option, event, contract, and extension seam introduced
- Avoid vague tasks, same-file conflicts, and cross-story dependencies that break independence
