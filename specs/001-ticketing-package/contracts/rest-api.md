# Contract: REST API

## Overview

The package exposes REST-style routes that host applications can enable selectively.
Controllers delegate to package actions/services and enforce policies through Laravel
authorization.

## Route Groups

- `api/ticketing/tickets`
- `api/ticketing/portal/tickets`
- `api/ticketing/staff/tickets`
- `api/ticketing/config/*` for administrative metadata where enabled

## Core Endpoints

### `POST /api/ticketing/tickets`

Creates a ticket from API or internal workflow context.

**Request shape**
- `subject`
- `description`
- `requester` object or host-resolved actor reference
- `category`
- `type`
- `priority`
- `custom_fields`
- `attachments`
- `tags`
- `watchers`

**Response**
- `201 Created`
- ticket resource including identifiers, current status, assignment, SLA deadlines,
  portal/staff visibility info, and links to conversation collection

### `GET /api/ticketing/tickets`

Returns filtered ticket collection for the authorized actor.

**Query filters**
- `status[]`, `priority[]`, `category[]`, `type[]`, `queue[]`, `assignee[]`, `tag[]`
- `search`
- `saved_view`
- `requester`
- `updated_after`, `updated_before`

### `GET /api/ticketing/tickets/{ticket}`

Returns one ticket with conversation, participants, SLA summary, tags, custom fields,
and current permissions.

### `PATCH /api/ticketing/tickets/{ticket}`

Updates editable ticket fields such as status, taxonomy, custom fields, and tags.

### `POST /api/ticketing/tickets/{ticket}/replies`

Adds a public reply or internal note.

**Request shape**
- `entry_type`
- `body`
- `attachments`
- `mentions`

### `POST /api/ticketing/tickets/{ticket}/assignments`

Creates an assignment to a user, team, or queue.

### `POST /api/ticketing/tickets/{ticket}/watchers`

Adds watchers/followers or updates watcher preferences.

### `POST /api/ticketing/tickets/{ticket}/status-transitions`

Executes a workflow-aware status change such as resolve or reopen.

### `GET /api/ticketing/metadata`

Returns categories, priorities, statuses, queues, form schema, and custom field
definitions visible to the caller.

## Resource Guarantees

- All resources include stable IDs and human-facing ticket numbers.
- Authorization failures return Laravel-standard forbidden responses.
- Validation failures surface field-level errors for forms and APIs.
- Ticket resources include visibility-safe audit summaries and available actions.
- Pagination uses Laravel paginator conventions.

## Versioning Expectations

- Initial release ships under package v1 public API expectations.
- New fields may be additive.
- Breaking route, payload, or semantic changes require major-version treatment.
