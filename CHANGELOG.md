# Changelog

All notable changes to this project are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Added Laravel-native, database-backed roles and permissions with protected `user`, `admin`, and `super-admin` roles, a module-owned permission catalog, idempotent synchronization, and minimized authorization activity events.
- Added explicit `admin.access` Inertia capability data without exposing role, permission, pivot, or internal database identifiers.
- Added an audited, idempotent Artisan workflow for verified-account super-admin provisioning and atomic rotation, with a dedicated operator runbook.
- Added driver-independent authorization-session versioning so super-admin grants, removals, and rotations revoke existing browser and remember-me sessions.

### Changed

- Changed Planning authorization to require the relevant permission and existing owner relationship at Laravel policy, controller, and Form Request boundaries.
- Changed development seed data to use an explicitly non-privileged demo account instead of super-admin-like credentials.

### Security

- Administrative roles receive explicit control-plane permissions without a universal super-admin bypass, so no administrative role gains access to another account's private Planning data.
- The final verified, active super-admin cannot lose the protected role, delete the account, or become unverified until another active operator exists.
- Permitted super-admin deletion or unverification now fails closed without activity capture and revokes existing browser and remember-me sessions.

## [2.1.4] - 2026-08-16

### Added

- Added an accessible finance-style application shell with semantic green light/dark themes, responsive desktop/tablet/mobile navigation, shared financial-number and state primitives, and visible keyboard focus behavior.
- Added exact-value Planning summaries, allocation composition, income/allocation, savings-target, category-magnitude, and multi-period trend graphs across Dashboard, Planning, and the read-only Expenses projection.

### Changed

- Changed Planning money persistence and browser payloads to canonical two-decimal strings backed by integer-sen calculations, deterministic half-up savings targets, bounded calendar periods, and one active plan per owner/period.
- Changed legacy Planning migration behavior to reject ambiguous months and lossy savings targets, normalize valid rows deterministically, and isolate the new fixed-precision cache namespace.
- Changed deletion feedback to identify the affected period and to preserve a committed database result when cache invalidation is temporarily unavailable.

### Fixed

- Fixed client-submitted totals and floating-point persistence so server-calculated income, savings, commitments, other allocations, spending, allocated total, and balance remain authoritative; new active duplicate periods are prevented, while existing duplicates stop migration for operator resolution.
- Fixed negative, partial, zero-denominator, and over-target presentation so graphs never invent percentages or exact values and always retain a readable tabular alternative.

### Security

- Preserved public-identifier ownership checks and CSRF protection for Planning deletion while returning generic failure messages and keeping exception details server-side.
- Kept numeric owner identifiers and internal Planning IDs out of browser payloads while maintaining account-isolated cache keys and projections.

### Deployment

- Before production rollout, back up and compare Planning rows and validate the migration and rollback on a disposable PostgreSQL database. The development release gate passed SQLite execution and PostgreSQL SQL review but did not run against a live PostgreSQL service.

## [2.1.3] - 2026-08-16

### Added

- Added a canonical banking-style UI/UX system covering responsive navigation, deterministic light/dark semantic tokens, financial number presentation, component states, motion, and WCAG 2.2 AA behavior.
- Added a complete Planning summary specification for exact figure hierarchy, accessible allocation graphs, page roles, responsive layouts, and legacy/error states.

### Changed

- Established remaining planned balance, income, savings allocation/target, commitments, and other allocations as the approved summary order, with total allocation kept as a lower-priority roll-up.
- Clarified that charts visualize monthly plans rather than bank transactions and must retain exact-value alternatives, zero-denominator behavior, and server-authoritative money boundaries.

## [2.1.2] - 2026-08-16

### Added

- Added deterministic exact-head local quality commands covering formatting, level-8 Larastan, backend/frontend tests, TypeScript, production builds, and locked dependency audits.
- Added the missing Inertia pages for password reset, password confirmation, and email verification, plus focused route and authorization regressions.
- Added bounded Composer/npm Dependabot updates targeting `v2.x` while keeping repository Actions disabled under the approved zero-cost policy.

### Changed

- Replaced implicit Planning/User model serialization with explicit public-ULID Inertia payloads across Dashboard, Planning, Expenses, and shared authentication.
- Documented locked dependency review, clean-checkout validation, test-database isolation, and the local-only release evidence policy.

### Fixed

- Fixed language controls to use Laravel's required `{language}` parameter in both layouts and regenerated Ziggy metadata.
- Fixed Expenses detail binding so owners can open their public-ULID plan projection while non-owners remain denied.
- Removed backend test dependence on pre-existing Vite assets and repaired inherited Auth/Profile rendering expectations.

### Security

- Cleared `DATABASE_URL` before tests so hostile shell configuration cannot redirect destructive test setup to an external database.
- Removed internal numeric Planning and user identifiers from Inertia props while preserving owner-scoped queries and policy checks.

## [2.1.1] - 2026-08-16

### Added

- Added evidence-backed references for the current stack, architecture, module boundaries, integrations, testing baseline, and verified concerns.
- Added canonical expense-planning vocabulary, current calculation/data flow, web/Inertia route and prop catalogs, and contract-first governance for a future API.
- Added focused frontend coverage for theme resolution, persistence, DOM state, layout wiring, storage failures, and pre-render initialization.

### Changed

- Updated the v2.x patch-release workflow to distinguish temporary version/issue branches, the v2.1.0 GitHub Release boundary, and bottom-up stacked-PR cleanup.
- Removed tracked macOS Finder metadata and made all seven codebase references directly navigable from the documentation map.

### Fixed

- Synchronized guest and authenticated theme controls through one shared component and deterministic preference path.
- Preserved operating-system theme fallback until a user explicitly selects a mode, applied saved modes before frontend rendering, and exposed a stable accessible toggle state.

## [2.1.0] - 2026-08-16

### Added

- Added the Laravel 12 modular application structure with an Inertia, React, and TypeScript frontend.
- Added PostgreSQL-backed monthly planning, public change summaries, operational diagnostics, activity history, and Planning cache guidance.

### Changed

- Replaced the earlier Livewire interface and MySQL development stack with the supported React/Inertia and PostgreSQL workflow.
- Standardized internal database IDs, public model identifiers, supported runtime versions, container images, release documentation, and issue-linked development practices.

### Security

- Removed known dependency advisories, restricted non-local diagnostics, minimized activity metadata, isolated Planning records by authenticated user, enforced trusted hosts, and constrained locale selection to shipped translations.

## [2.0.9] - 2026-08-15

### Added

- Added user-scoped caching for Planning collections shared by the Dashboard, Planning, and Expenses pages.
- Added operator guidance for Planning cache inspection and invalidation, plus a canonical development and release workflow.

### Changed

- Repeated Planning collection reads now reuse a five-minute cache entry and refresh it after successful Planning or account lifecycle changes.

### Security

- Isolated cached Planning data by authenticated user and kept single-record authorization, authentication, profile, diagnostics, failures, and errors outside the cache.

## [2.0.8] - 2026-08-15

### Added

- Added durable server-side activity history for account registration, profile updates, account deletion, and Planning creation and deletion.
- Added an operator guide for activity-log inspection, retention, incident response, and rollback.

### Changed

- Added configurable daily pruning with a default retention period of 365 days.

### Security

- Limited activity entries to public identifiers and allowlisted metadata, and made capture atomic with each successful state change.

## [2.0.7] - 2026-08-15

### Added

- Added a canonical documentation map, repository-wide contributor guidance, and an operator runbook for error diagnostics and incident response.

### Changed

- Replaced the development Telescope pruning loop with configurable daily retention.
- Limited non-local diagnostics to reportable failures, scheduled tasks, monitored entries, and error-level logs.

### Security

- Added explicit non-local Telescope enablement, verified-operator authorization, fail-closed configuration, and recursive credential redaction.

## [2.0.6] - 2026-08-15

### Added

- Added a public changes summary to the login page.
- Added a documented backup, restore, validation, and rollback path for PostgreSQL 18 upgrades.

### Changed

- Restored ID-based links for planning and expense pages.
- Updated the supported PHP and Node.js toolchain and pinned local PostgreSQL, Redis, and pgAdmin service versions.

### Security

- Updated Composer and npm dependencies to remove known security advisories while remaining on the intended major versions.

[Unreleased]: https://github.com/lurzar/expenses-app/compare/v2.1.4-dev...HEAD
[2.1.4]: https://github.com/lurzar/expenses-app/compare/v2.1.3-dev...v2.1.4-dev
[2.1.3]: https://github.com/lurzar/expenses-app/compare/v2.1.2-dev...v2.1.3-dev
[2.1.2]: https://github.com/lurzar/expenses-app/compare/v2.1.1-dev...v2.1.2-dev
[2.1.1]: https://github.com/lurzar/expenses-app/compare/v2.1.0-dev...v2.1.1-dev
[2.1.0]: https://github.com/lurzar/expenses-app/compare/v2.0.9-dev...v2.1.0-dev
[2.0.9]: https://github.com/lurzar/expenses-app/compare/v2.0.8-dev...v2.0.9-dev
[2.0.8]: https://github.com/lurzar/expenses-app/compare/v2.0.7-dev...v2.0.8-dev
[2.0.7]: https://github.com/lurzar/expenses-app/compare/v2.0.6-dev...v2.0.7-dev
[2.0.6]: https://github.com/lurzar/expenses-app/compare/v2.0.5-dev...v2.0.6-dev
