---

description: "Task list template for feature implementation"
---

# Tasks: [FEATURE NAME]

**Input**: Design documents from `/specs/[###-feature-name]/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, contracts/

**Tests**: Every feature MUST include the necessary unit, feature, and integration
tests required by the constitution. Omit a level only when the plan explicitly
justifies why it is not applicable.

**Organization**: Tasks are grouped by user story to enable independent implementation
and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions

- **Laravel package**: `src/`, `config/`, `database/`, `routes/`, `tests/` at repository root
- **Split package + workbench app**: `package/` and `workbench/` or `example-app/`
- Paths shown below assume a Laravel package at repository root; adjust based on
  `plan.md`

<!--
  ============================================================================
  IMPORTANT: The tasks below are SAMPLE TASKS for illustration purposes only.

  The /speckit.tasks command MUST replace these with actual tasks based on:
  - User stories from spec.md (with their priorities P1, P2, P3...)
  - Feature requirements from plan.md
  - Entities from data-model.md
  - Endpoints from contracts/

  Tasks MUST be organized by user story so each story can be:
  - Implemented independently
  - Tested independently
  - Delivered as an MVP increment

  DO NOT keep these sample tasks in the generated tasks.md file.
  ============================================================================
-->

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [ ] T001 Create package structure per implementation plan
- [ ] T002 Initialize Composer, Laravel package dependencies, and test harness
- [ ] T003 [P] Configure linting, formatting, and static analysis tools

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**CRITICAL**: No user story work can begin until this phase is complete

Examples of foundational tasks (adjust based on your project):

- [ ] T004 Setup database schema and migrations framework in `database/migrations/`
- [ ] T005 [P] Implement authorization framework in `src/Policies/` or related contracts
- [ ] T006 [P] Define package contracts, extension points, and container bindings in `src/Contracts/`
- [ ] T007 [P] Setup package service provider, config, and publishable resources
- [ ] T008 Create base models/entities that all stories depend on in `src/Models/`
- [ ] T009 Configure audit/logging and notification infrastructure
- [ ] T010 Setup supported-version compatibility verification

**Checkpoint**: Foundation ready; user story implementation can now begin in parallel

---

## Phase 3: User Story 1 - [Title] (Priority: P1) MVP

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 1

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [ ] T011 [P] [US1] Unit tests for [class/behavior] in `tests/Unit/[Name]Test.php`
- [ ] T012 [P] [US1] Feature tests for [user flow/API surface] in `tests/Feature/[Name]Test.php`
- [ ] T013 [P] [US1] Integration tests for [Laravel app/package interaction] in `tests/Integration/[Name]Test.php`

### Implementation for User Story 1

- [ ] T014 [P] [US1] Create or update model in `src/Models/[Entity].php`
- [ ] T015 [P] [US1] Create or update contract in `src/Contracts/[Name].php`
- [ ] T016 [US1] Implement action/service in `src/Actions/[Name].php`
- [ ] T017 [US1] Implement feature surface in `src/[Location]/[File].php`
- [ ] T018 [US1] Add schema, authorization, notification, and auditability handling required for this story
- [ ] T019 [US1] Document config, events, contracts, and extension points introduced by this story

**Checkpoint**: User Story 1 should now be fully functional and testable independently

---

## Phase 4: User Story 2 - [Title] (Priority: P2)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 2

- [ ] T020 [P] [US2] Unit tests in `tests/Unit/[Name]Test.php`
- [ ] T021 [P] [US2] Feature tests in `tests/Feature/[Name]Test.php`
- [ ] T022 [P] [US2] Integration tests in `tests/Integration/[Name]Test.php`

### Implementation for User Story 2

- [ ] T023 [P] [US2] Create or update domain model/contract in `src/[Location]/[File].php`
- [ ] T024 [US2] Implement action/listener/notification/job in `src/[Location]/[File].php`
- [ ] T025 [US2] Implement feature surface and package integration points in `src/[Location]/[File].php`
- [ ] T026 [US2] Integrate with User Story 1 components while preserving public API stability
- [ ] T027 [US2] Document config, events, contracts, and extension points introduced by this story

**Checkpoint**: User Stories 1 and 2 should both work independently

---

## Phase 5: User Story 3 - [Title] (Priority: P3)

**Goal**: [Brief description of what this story delivers]

**Independent Test**: [How to verify this story works on its own]

### Tests for User Story 3

- [ ] T028 [P] [US3] Unit tests in `tests/Unit/[Name]Test.php`
- [ ] T029 [P] [US3] Feature tests in `tests/Feature/[Name]Test.php`
- [ ] T030 [P] [US3] Integration tests in `tests/Integration/[Name]Test.php`

### Implementation for User Story 3

- [ ] T031 [P] [US3] Create or update domain model/contract in `src/[Location]/[File].php`
- [ ] T032 [US3] Implement action/listener/notification/job in `src/[Location]/[File].php`
- [ ] T033 [US3] Implement feature surface and package integration points in `src/[Location]/[File].php`
- [ ] T034 [US3] Document config, events, contracts, and extension points introduced by this story

**Checkpoint**: All user stories should now be independently functional

---

[Add more user story phases as needed, following the same pattern]

---

## Phase N: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [ ] TXXX [P] Documentation updates in `docs/`, `README.md`, or generated references
- [ ] TXXX Code cleanup and refactoring
- [ ] TXXX Performance optimization across all stories
- [ ] TXXX [P] Compatibility matrix verification across supported Laravel and PHP versions
- [ ] TXXX [P] Additional unit, feature, and integration tests where coverage gaps remain
- [ ] TXXX Security hardening
- [ ] TXXX Public API and upgrade-note review for semantic versioning impact
- [ ] TXXX Run `quickstart.md` validation

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies; can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion; blocks all user stories
- **User Stories (Phase 3+)**: Depend on Foundational phase completion
- **Polish (Final Phase)**: Depends on all desired user stories being complete

### User Story Dependencies

- **User Story 1 (P1)**: Can start after Foundational; no dependencies on other stories
- **User Story 2 (P2)**: Can start after Foundational; may integrate with US1 but should be independently testable
- **User Story 3 (P3)**: Can start after Foundational; may integrate with US1/US2 but should be independently testable

### Within Each User Story

- Unit, feature, and integration tests MUST be written and FAIL before implementation
- Contracts and models before actions/services where applicable
- Actions/services before endpoints, listeners, jobs, or notifications
- Core implementation before documentation sign-off
- Story complete before moving to next priority

### Parallel Opportunities

- All Setup tasks marked `[P]` can run in parallel
- All Foundational tasks marked `[P]` can run in parallel within Phase 2
- Once Foundational completes, user stories can start in parallel if team capacity allows
- Test tasks for a user story marked `[P]` can run in parallel
- Models and contracts within a story marked `[P]` can run in parallel

---

## Parallel Example: User Story 1

```bash
# Launch all tests for User Story 1 together:
Task: "Unit tests in tests/Unit/[Name]Test.php"
Task: "Feature tests in tests/Feature/[Name]Test.php"
Task: "Integration tests in tests/Integration/[Name]Test.php"

# Launch all foundational domain pieces for User Story 1 together:
Task: "Create model in src/Models/[Entity].php"
Task: "Create contract in src/Contracts/[Name].php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: User Story 1
4. Stop and validate User Story 1 independently
5. Release/demo if ready

### Incremental Delivery

1. Complete Setup and Foundational
2. Add User Story 1 and validate it independently
3. Add User Story 2 and validate it independently
4. Add User Story 3 and validate it independently
5. Ensure each story adds value without breaking previous stories

### Parallel Team Strategy

1. Team completes Setup and Foundational together
2. Once Foundational is done, stories can be staffed independently
3. Integrate only through explicit package contracts and documented extension seams

---

## Notes

- `[P]` tasks = different files, no dependencies
- `[Story]` label maps task to a specific user story for traceability
- Each user story should be independently completable and testable
- Verify required tests fail before implementing
- Include documentation tasks for every config option, event, contract, and extension seam introduced
- Stop at checkpoints to validate stories independently
- Avoid vague tasks, same-file conflicts, and cross-story dependencies that break independence
