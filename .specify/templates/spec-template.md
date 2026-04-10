# Feature Specification: [FEATURE NAME]

**Feature Branch**: `[###-feature-name]`  
**Created**: [DATE]  
**Status**: Draft  
**Input**: User description: "$ARGUMENTS"

## User Scenarios & Testing *(mandatory)*

<!--
  IMPORTANT: User stories should be PRIORITIZED as user journeys ordered by importance.
  Each user story/journey must be INDEPENDENTLY TESTABLE - meaning if you implement
  just ONE of them, you should still have a viable MVP that delivers value.
-->

### User Story 1 - [Brief Title] (Priority: P1)

[Describe this user journey in plain language]

**Why this priority**: [Explain the value and why it has this priority level]

**Independent Test**: [Describe how this can be tested independently]

**Acceptance Scenarios**:

1. **Given** [initial state], **When** [action], **Then** [expected outcome]
2. **Given** [initial state], **When** [action], **Then** [expected outcome]

---

### User Story 2 - [Brief Title] (Priority: P2)

[Describe this user journey in plain language]

**Why this priority**: [Explain the value and why it has this priority level]

**Independent Test**: [Describe how this can be tested independently]

**Acceptance Scenarios**:

1. **Given** [initial state], **When** [action], **Then** [expected outcome]

---

### User Story 3 - [Brief Title] (Priority: P3)

[Describe this user journey in plain language]

**Why this priority**: [Explain the value and why it has this priority level]

**Independent Test**: [Describe how this can be tested independently]

**Acceptance Scenarios**:

1. **Given** [initial state], **When** [action], **Then** [expected outcome]

---

[Add more user stories as needed, each with an assigned priority]

### Required Test Coverage

- Unit coverage required for: [list classes, rules, value objects, policies, helpers]
- Feature coverage required for: [list package entry points, HTTP flows, console commands, notifications]
- Integration coverage required for: [list Laravel-app integration points, service provider wiring, publishing, migrations]

### Edge Cases

- What happens when [boundary condition]?
- How does system handle [error scenario]?

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST [specific capability]
- **FR-002**: System MUST [specific capability]
- **FR-003**: Users MUST be able to [key interaction]
- **FR-004**: System MUST [data requirement]
- **FR-005**: System MUST [behavior]

*Example of marking unclear requirements:*

- **FR-006**: System MUST authenticate users via
  [NEEDS CLARIFICATION: auth method not specified]
- **FR-007**: System MUST retain user data for
  [NEEDS CLARIFICATION: retention period not specified]

### Compatibility & Package Fit *(mandatory for package work)*

- Supported PHP versions: [list or NEEDS CLARIFICATION]
- Supported Laravel versions: [list or NEEDS CLARIFICATION]
- Installation or upgrade impact on an existing Laravel app:
  [describe setup, publishing, migrations, env/config changes]
- Breaking change risk to public APIs: [None / describe impact and intended versioning]

### Extension Surface *(mandatory for package work)*

- Config options introduced or changed: [list each option or state None]
- Events dispatched or listened to: [list each event or state None]
- Contracts, interfaces, or abstract classes added or changed: [list or state None]
- Publishable assets/resources: [config, migrations, translations, views, routes, etc.]
- Consumer override/customization points:
  [bindings, callbacks, policies, repositories, listeners, notifications]

### Operational Quality Concerns *(mandatory when applicable)*

- Database schema impact: [None / describe migrations, indexes, constraints, retention]
- Authorization impact: [None / describe policies, gates, role assumptions]
- Notification impact: [None / describe channels, delivery rules, opt-outs]
- Auditability impact: [None / describe events, logs, audit trail, actor attribution]

### Key Entities *(include if feature involves data)*

- **[Entity 1]**: [What it represents, key attributes without implementation]
- **[Entity 2]**: [What it represents, relationships to other entities]

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: [Measurable metric]
- **SC-002**: [Measurable metric]
- **SC-003**: [User or operator outcome]
- **SC-004**: [Upgrade, adoption, or supportability outcome]

## Assumptions

- [Assumption about target users]
- [Assumption about scope boundaries]
- [Assumption about data/environment]
- [Dependency on existing system/service]

## Documentation Impact *(mandatory)*

- User-facing documentation to add/update: [README sections, quickstart, examples]
- Developer-facing documentation to add/update: [contracts, events, extension points, config reference]
- Migration or deprecation notes required: [None / describe]
