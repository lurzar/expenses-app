# Changelog

All notable changes to this project are documented in this file.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project uses [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.1.0] - 2026-08-16

### Added

- Added the Laravel 12 modular application structure with an Inertia, React, and TypeScript frontend.
- Added PostgreSQL-backed monthly planning, public change summaries, operational diagnostics, activity history, and Planning cache guidance.

### Changed

- Replaced the earlier Livewire interface and MySQL development stack with the supported React/Inertia and PostgreSQL workflow.
- Standardized internal database IDs, public model identifiers, supported runtime versions, container images, release documentation, and issue-linked development practices.

### Security

- Removed known dependency advisories, restricted non-local diagnostics, minimized activity metadata, and isolated cached Planning collections by authenticated user.

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

[Unreleased]: https://github.com/lurzar/expenses-app/compare/v2.1.0-dev...HEAD
[2.1.0]: https://github.com/lurzar/expenses-app/compare/v2.0.9-dev...v2.1.0-dev
[2.0.9]: https://github.com/lurzar/expenses-app/compare/v2.0.8-dev...v2.0.9-dev
[2.0.8]: https://github.com/lurzar/expenses-app/compare/v2.0.7-dev...v2.0.8-dev
[2.0.7]: https://github.com/lurzar/expenses-app/compare/v2.0.6-dev...v2.0.7-dev
[2.0.6]: https://github.com/lurzar/expenses-app/compare/v2.0.5-dev...v2.0.6-dev
