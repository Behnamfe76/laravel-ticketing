# Upgrade Guide

This is the initial package implementation, so there are no historical migrations or
deprecations yet. Future releases should use the policy below.

## Public API Rules

Treat the following as versioned public API:

- Container contracts in `src/Contracts`
- Config keys in `config/ticketing.php` and `config/ticketing-permissions.php`
- Route names and route payload semantics
- Event class names and payload properties
- Database table and column names from package migrations
- Publish tags and install command behavior

## Patch Releases

Patch releases may include bug fixes, additional tests, internal refactors, and
documentation improvements. They must not require host application code changes.

## Minor Releases

Minor releases may add optional config keys, routes, contracts, events, fields,
notifications, or resources. Defaults must preserve existing behavior.

## Major Releases

Major releases are required for removed contracts, renamed config keys, incompatible
route payloads, migration-breaking schema changes, or changed authorization semantics.
Every major release should include:

- Changed public APIs
- Required config changes
- Required migration steps
- Backward-incompatible behavior notes
- Test guidance for host applications

## Host Application Checklist

Before upgrading:

1. Run the package and host application test suites.
2. Check custom bindings for contracts listed in `docs/extensibility.md`.
3. Review published config for new keys.
4. Run package migrations in a staging environment.
5. Verify portal, staff, API, email, queue, and reporting flows that are enabled in the
   host application.
