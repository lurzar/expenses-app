# ADR 0002: Modular Laravel authorization with Spatie RBAC

- Status: Accepted for v2.2.0
- Date: 2026-08-16
- Owners: Authorization/system maintainers
- Issue: [#131](https://github.com/lurzar/expenses-app/issues/131)
- Parent: [#130](https://github.com/lurzar/expenses-app/issues/130)

## Context

Expenses App authenticates users with Laravel's `web` guard and protects direct Planning records with a registered Laravel policy. It has no database-backed roles, reusable permission catalog, Admin authorization boundary, or production super-admin lifecycle. Adding those concerns separately inside each future module would scatter role checks, duplicate persistence, and make privilege review difficult.

The authorization design must support the current modular monolith and a future commercial control plane without introducing billing or tenancy now. It must preserve the existing rule that a user may access only their own Planning records. Browser-visible roles or capability props may improve navigation, but Laravel must remain the enforcement boundary.

The repository currently locks Laravel 12.66.0 and a PHP 8.4.0 Composer platform. On 2026-08-16, `spatie/laravel-permission` 8.3.0 is the current signed release. Its package metadata requires PHP 8.3 or newer and Illuminate 12 or 13, so its maintained v8 line matches the repository. The package registers permissions with Laravel's Gate and supports roles as permission groups.

## Decision

1. Use `spatie/laravel-permission` v8 as the database-backed role, permission, assignment, and cache engine. Issue #13 resolved `^8.3` to 8.3.0 and locked the reviewed dependency graph; later updates remain subject to release-note review, focused authorization regressions, and repository audits.
2. Keep Laravel's authorization APIs as the application contract. Routes use `can` middleware where it rejects a request before controller work; controllers use `Gate::authorize()` for bound resources; Form Requests authorize submitted mutations; model policies combine capabilities with record context.
3. Create an application-owned Authorization module. It owns package configuration, the permission catalog, protected-role definitions, synchronization, shared invariants, and authorization migrations. Feature modules continue to own their policies and permission declarations.
4. Declare permissions in code as module-owned backed enums with stable lowercase dot-separated values such as `planning.view`, `admin.access`, and `roles.manage`. Code and translations own module grouping and labels. Database rows support assignment and lookup; the Admin UI cannot invent permission names.
5. Use one `web` guard. The package may use the existing internal numeric `users.id` for assignment pivots because public `user_id` ULIDs are browser identifiers, not Eloquent primary keys. Role and permission database IDs remain internal and never enter Inertia payloads.
6. Assign permissions to roles and roles to users. The protected roles are `user`, `admin`, and `super-admin`. Application services do not assign direct permissions to users. An account may hold the base `user` role plus an administrative role.
7. Treat permissions and record ownership as separate requirements. A Planning policy must require the relevant capability and the existing owner relationship. Administrative roles do not bypass this condition unless a later issue approves a narrowly defined, audited support-access workflow.
8. Do not register a universal `Gate::before` super-admin bypass. The `super-admin` role receives explicit control-plane permissions from the code-owned catalog. This preserves Laravel's normal Gate flow without turning every current or future model policy into unconditional private-data access.
9. Synchronize the catalog idempotently. Synchronization may create or update declared permissions and protected role mappings, but it must not silently delete unknown database permissions, roles, or user assignments. It reports drift for operator resolution and clears the package permission cache after a successful change.
10. Share only page-relevant capability booleans or allowlisted ability maps with Inertia. Global shared props do not expose the full catalog, role/permission IDs, or raw package models. React may hide unavailable actions, but direct server requests must still fail safely.
11. Record privileged catalog, role, permission, and assignment changes through the ActivityLog module with allowlisted event names and minimized metadata. Logs exclude credentials, sessions, emails, internal keys, and Planning values.
12. Defer direct user permissions, teams/tenant-scoped roles, organizations, billing, subscriptions, API-token permissions, support impersonation, wildcard permissions, and user-authored authorization rules. Each requires a later issue and, where the trust boundary changes, a new ADR.

The detailed module contract lives in [`../codebase/AUTHORIZATION.md`](../codebase/AUTHORIZATION.md).

## Alternatives considered

### Store one role column on `users`

A role column would be simple for `user` and `admin`, but it cannot express module capabilities, custom role composition, multiple administrative responsibilities, or future Admin-managed mappings without repeated schema and conditional changes. It also encourages role-name checks instead of permission-based Laravel authorization.

### Build application-specific RBAC tables and cache behavior

A local implementation could match the current schema exactly, but it would duplicate mature role/permission relationships, Gate registration, cache invalidation, guard handling, and assignment APIs. That work adds security-sensitive maintenance without a product-specific advantage. Application-owned policies, catalog rules, synchronization, auditing, and privacy boundaries remain necessary with either approach, so the package removes infrastructure rather than domain control.

### Use only Laravel Gates and policies without persisted roles

Laravel Gates and policies remain the enforcement layer, but static Gate definitions alone do not provide persisted role composition or an Admin workflow for assigning approved capabilities. This approach cannot meet #130 without building the RBAC storage described above.

### Use the older package v6 line

The package documents v6 as compatible with Laravel 12, but v8 is the maintained major for Laravel 12/13 and PHP 8.3 or newer. The repository already requires PHP 8.4. Starting on v6 would accept avoidable upgrade work and older behavior.

### Grant super-admin every ability through `Gate::before`

The package recommends `Gate::before` for applications where super-admin should pass every authorization check. Expenses App has a stronger privacy requirement: platform administration must not imply access to another user's Planning data. Explicit control-plane permissions keep this boundary visible and testable.

## Consequences

- Future modules add a permission enum, register it with the central catalog, implement Laravel policies or Gates, and add allow/deny tests. They do not create a second role system.
- Admin can compose roles from registered permissions after #134, while permission creation remains tied to reviewed code.
- The application gains a maintained external dependency, package-owned tables, and package cache behavior. Dependency upgrades require release-note review, locked audits, migration review, and focused authorization regressions.
- Package APIs must perform role/permission mutations. Direct database writes risk stale cache and bypass package invariants.
- Unknown catalog rows require operator review rather than automatic deletion. This favors access preservation during rollback and mixed-version deployment.
- The protected-role lifecycle and final-super-admin recovery remain separate implementation work in #132.
- The current `web` guard avoids duplicate role/permission namespaces. A future API guard or tenant scope requires an explicit design and migration.
- Existing public ULIDs and Inertia data-minimization rules remain unchanged.

## Deployment and rollback

Issue #13 must validate fresh installation, representative existing-user migration, idempotent catalog synchronization, package cache invalidation, and migration rollback in isolated SQLite plus PostgreSQL where schema semantics differ. Existing users and Planning records must survive. Unknown roles, permissions, and assignments must stop destructive cleanup and produce an operator-visible report.

Before deployment, back up the affected authorization and user tables, verify the exact locked package version and audits, run migrations before enabling authorization-dependent routes, synchronize the catalog, clear/rebuild the permission cache, and verify at least one protected operator through a disposable or approved environment. Roll application usage back before removing package schema. Never drop assignment tables or remove the final super-admin as an automatic rollback step.

## References

- [Laravel 12 authorization](https://laravel.com/docs/12.x/authorization)
- [Spatie Laravel Permission v8 introduction](https://spatie.be/docs/laravel-permission/v8/introduction)
- [Spatie v8 prerequisites and compatibility](https://spatie.be/docs/laravel-permission/v8/prerequisites)
- [Spatie roles versus permissions](https://spatie.be/docs/laravel-permission/v8/best-practices/roles-vs-permissions)
- [Spatie model policy guidance](https://spatie.be/docs/laravel-permission/v8/best-practices/using-policies)
- [Spatie super-admin guidance](https://spatie.be/docs/laravel-permission/v8/basic-usage/super-admin)
- [Spatie Laravel Permission 8.3.0 release](https://github.com/spatie/laravel-permission/releases/tag/8.3.0)
