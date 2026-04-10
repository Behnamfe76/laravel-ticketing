# Data Model: Laravel Ticketing Package

## Ticket

**Purpose**: Core support work item representing a requester issue, task, or service request.

**Fields**
- `id`
- `tenant_id` nullable, tenant-scope foreign identifier or morph key
- `number` unique human-facing ticket reference
- `subject`
- `description` nullable canonical opening body
- `requester_type`, `requester_id` morph relationship
- `creator_type`, `creator_id` morph relationship
- `status_id`
- `priority_id` nullable
- `category_id` nullable
- `type_id` nullable
- `current_assignment_id` nullable
- `sla_policy_id` nullable
- `first_response_due_at` nullable
- `resolution_due_at` nullable
- `first_responded_at` nullable
- `resolved_at` nullable
- `closed_at` nullable
- `last_activity_at` nullable
- `source` enum-like string (`portal`, `staff`, `workflow`, `api`, `email`)
- `visibility` enum-like string for portal/public access model
- `search_document` nullable denormalized text/index source
- `meta` json
- timestamps, soft deletes optional by package policy

**Relationships**
- belongs to status, priority, category, type
- belongs to SLA policy
- has many conversation entries
- has many attachments through ticket or conversations
- has many assignments
- has many watchers/followers
- has many tags through pivot
- has many custom field values
- has many audit records

**Validation Rules**
- subject required
- requester required for end-user-facing flows
- status must be valid for workflow
- taxonomy values must belong to same tenant/global scope

**State Transitions**
- `new -> open -> pending -> resolved -> closed`
- reopen allowed from `resolved` or `closed` by policy/config
- custom workflow graphs supported through status transition rules

## ConversationEntry

**Purpose**: Threaded reply, internal note, or system-generated message attached to a ticket.

**Fields**
- `id`
- `ticket_id`
- `tenant_id` nullable
- `author_type`, `author_id` nullable morph
- `entry_type` (`public_reply`, `internal_note`, `system`)
- `body`
- `body_format` nullable
- `source` (`portal`, `staff`, `api`, `email`, `automation`, `system`)
- `visibility_scope`
- `in_reply_to_id` nullable self-reference
- `email_message_id` nullable
- `meta` json
- timestamps

**Relationships**
- belongs to ticket
- belongs to author morph
- has many attachments
- has many mention records
- has many audit records or linked audit events

**Validation Rules**
- body required except some system entries
- entry type must be allowed for acting user and route
- visibility must match ticket/actor permissions

## Attachment

**Purpose**: File metadata for ticket- or conversation-scoped uploads.

**Fields**
- `id`
- `tenant_id` nullable
- `attachable_type`, `attachable_id` morph
- `uploaded_by_type`, `uploaded_by_id` nullable morph
- `disk`
- `path`
- `original_name`
- `mime_type`
- `size_bytes`
- `checksum` nullable
- `visibility_scope`
- `meta` json
- timestamps

**Relationships**
- morph to ticket or conversation entry
- morph to uploader

**Validation Rules**
- disk/path required
- size and type constrained by config and field rules

## Assignment

**Purpose**: Immutable history of ticket ownership/routing changes.

**Fields**
- `id`
- `ticket_id`
- `tenant_id` nullable
- `target_type` (`user`, `team`, `queue`)
- `target_id`
- `assigned_by_type`, `assigned_by_id` nullable morph
- `reason` nullable
- `is_current`
- `assigned_at`
- `released_at` nullable
- `meta` json

**Relationships**
- belongs to ticket
- polymorphic target to assignee model/queue model/team model

**Validation Rules**
- one current assignment per ticket
- target must be assignable in tenant/workflow context

## Queue

**Purpose**: Operational routing bucket for tickets.

**Fields**
- `id`
- `tenant_id` nullable
- `name`
- `slug`
- `description` nullable
- `is_active`
- `default_assignee_type`, `default_assignee_id` nullable morph
- `settings` json
- timestamps

**Relationships**
- has many assignments
- belongs to many members/teams through pivots
- has many automation rules and SLA bindings

## Team

**Purpose**: Group of staff users eligible for assignment or queue membership.

**Fields**
- `id`
- `tenant_id` nullable
- `name`
- `slug`
- `description` nullable
- `settings` json
- timestamps

**Relationships**
- belongs to many staff actors
- belongs to many queues

## Status

**Purpose**: Workflow state definition for tickets.

**Fields**
- `id`
- `tenant_id` nullable
- `name`
- `slug`
- `kind` (`open`, `pending`, `resolved`, `closed`, custom)
- `color` nullable
- `sort_order`
- `is_default`
- `is_terminal`
- `transitions` json
- timestamps

## Priority

**Purpose**: Urgency/severity taxonomy value.

**Fields**
- `id`
- `tenant_id` nullable
- `name`
- `slug`
- `sort_order`
- `response_target_minutes` nullable
- `resolution_target_minutes` nullable
- `meta` json

## Category and TicketType

**Purpose**: Classification taxonomy for routing, forms, automation, and reporting.

**Fields**
- `id`
- `tenant_id` nullable
- `name`
- `slug`
- `description` nullable
- `is_active`
- `settings` json

## Tag

**Purpose**: Lightweight, many-to-many label for filtering and search.

**Fields**
- `id`
- `tenant_id` nullable
- `name`
- `slug`
- `color` nullable

## Watcher

**Purpose**: Participant following ticket activity for visibility or notification.

**Fields**
- `id`
- `ticket_id`
- `tenant_id` nullable
- `actor_type`, `actor_id` morph
- `relation_type` (`watcher`, `follower`, `mentioned`)
- `notification_preferences` json
- timestamps

## CustomFieldDefinition

**Purpose**: Schema for dynamic ticket/request data.

**Fields**
- `id`
- `tenant_id` nullable
- `scope` (`ticket`, `reply`, `form`)
- `name`
- `slug`
- `field_type`
- `label`
- `help_text` nullable
- `is_required`
- `is_active`
- `validation_rules` json
- `options` json
- `display_order`
- `visibility_rules` json
- timestamps

## CustomFieldValue

**Purpose**: Persisted dynamic values attached to tickets or replies.

**Fields**
- `id`
- `field_definition_id`
- `valuable_type`, `valuable_id` morph
- `tenant_id` nullable
- `value` json
- `normalized_value` nullable
- timestamps

## SLAPolicy

**Purpose**: Rule set defining response and resolution obligations.

**Fields**
- `id`
- `tenant_id` nullable
- `name`
- `slug`
- `description` nullable
- `conditions` json
- `response_target_minutes`
- `resolution_target_minutes`
- `calendar_rules` json
- `escalation_rule_id` nullable
- `is_active`
- timestamps

## AutomationRule

**Purpose**: Configurable trigger-condition-action rule.

**Fields**
- `id`
- `tenant_id` nullable
- `name`
- `slug`
- `trigger` (`ticket_created`, `ticket_updated`, `sla_threshold`, `reply_added`, etc.)
- `conditions` json
- `actions` json
- `priority`
- `is_active`
- `stop_processing`
- timestamps

## SavedView

**Purpose**: Reusable search/filter preset.

**Fields**
- `id`
- `tenant_id` nullable
- `owner_type`, `owner_id` nullable morph
- `name`
- `slug`
- `scope` (`private`, `team`, `global`)
- `filters` json
- `columns` json nullable
- `sort` json nullable
- timestamps

## AuditRecord

**Purpose**: Immutable activity ledger for ticketing actions.

**Fields**
- `id`
- `tenant_id` nullable
- `ticket_id` nullable
- `actor_type`, `actor_id` nullable morph
- `event_name`
- `subject_type`, `subject_id` nullable morph
- `old_values` json nullable
- `new_values` json nullable
- `context` json
- `occurred_at`

## EmailThread

**Purpose**: Track inbound/outbound mail correlation for tickets.

**Fields**
- `id`
- `tenant_id` nullable
- `ticket_id`
- `message_id`
- `in_reply_to` nullable
- `references` json nullable
- `direction` (`inbound`, `outbound`)
- `sender_address`
- `recipient_addresses` json
- `processed_at` nullable
- `status`
- `meta` json

## ReportingProjection Hook Inputs

**Purpose**: Stable read contracts for metrics consumers.

**Shape**
- ticket identifiers and tenant scope
- timestamps for creation, response, resolution, close
- assignment snapshots
- taxonomy snapshots
- SLA status snapshots
- aggregate conversation counts
- notification counts where available
