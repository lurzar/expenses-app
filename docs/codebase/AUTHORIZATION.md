# Authorization contract

This reference defines the v2.2.0 authorization contract implemented by issue [#13](https://github.com/lurzar/expenses-app/issues/13). [ADR 0002](../decisions/0002-modular-laravel-authorization.md) records the decision and alternatives. Dependent Admin screens and the production super-admin lifecycle remain separate issue-backed work.

## Trust model

Laravel is the authorization authority. The browser receives only enough capability data to present available navigation and actions. Hiding a React component never grants or denies access.

Authorization answers two different questions:

1. **Permission:** may this user perform this kind of action?
2. **Policy context:** may this user perform it on this specific record?

Both conditions apply to private domain records. For example, `planning.delete` permits the action type, while `PlanningPolicy::delete()` also requires the authenticated user to own the Planning record. `admin` and `super-admin` do not remove that ownership rule.

## Laravel enforcement path

Use the earliest relevant Laravel boundary and keep server-side defense at the mutation or record boundary:

```text
request
  -> auth/verified middleware
  -> can middleware for route-level abilities
  -> FormRequest::authorize() for submitted operations
  -> Gate::authorize() / model policy for a bound record
  -> typed module service and transaction
  -> minimized activity event
  -> explicit Inertia response capabilities
```

- Use Gates for non-model abilities such as `admin.access`.
- Use policies for Eloquent resources and ownership/context decisions.
- Use `can` middleware when the route has one clear ability that can be checked before controller work.
- Use Form Request authorization for request-level mutations when the decision depends on the authenticated user and route or submitted context; keep validation as a separate step.
- Use `Gate::authorize()` in a controller when authorization depends on a bound record or explicit context.
- Use `$user->can()` and `canAny()` for application checks. Do not spread package-specific `hasPermissionTo()` calls through modules.
- Check roles directly only inside protected-role lifecycle code where the role itself is the subject of the operation.

Controllers still authorize even when the UI hides an action. Client capability props are a presentation contract, not a security control.

## Ownership

Permissions never replace ownership. A policy combines the permission with record context. Collection queries also remain scoped to the authenticated owner unless an approved administrative use case defines a separate query, permission, data contract, and audit event.

Expenses remains a read-only projection of Planning. Administrative access does not create a transaction ledger or a private-financial-data viewer.

## Roles

The initial protected roles are:

| Role | Purpose | Boundary |
| --- | --- | --- |
| `user` | Base application capabilities for an account | Own Planning data only |
| `admin` | Approved day-to-day control-plane capabilities | No automatic private Planning access |
| `super-admin` | Protected platform ownership, recovery, and role/permission administration | Explicit control-plane permissions; no universal Gate bypass |

An administrative account also retains the base `user` role when it uses personal Planning features. Roles group permissions; the application does not grant direct user permissions in v2.2.0.

Protected roles have code-owned identifiers and lifecycle rules. Admin screens may display them, but cannot rename or retire them. Issue #132 owns initial provisioning, rotation, final-super-admin protection, and recovery.

## Permission catalog

Each module declares permissions in a string-backed enum under its own boundary:

```text
app/Modules/<Module>/Permissions/<Module>Permission.php
```

Permission values use lowercase dot-separated names:

```text
<module>.<action>
```

Examples:

- `planning.view`
- `planning.create`
- `planning.delete`
- `admin.access`
- `users.view`
- `users.assign-role`
- `roles.manage`

Use a specific verb when one permission should not grant every mutation. Prefer `roles.view`, `roles.create`, and `roles.assign-permissions` over a broad `roles.manage` when the UI or policy needs those actions independently.

Each declaration supplies code-owned metadata:

- stable permission value;
- owning module;
- translation key for its label and description;
- classification as account/domain or control-plane access; and
- active catalog membership; retiring a permission requires a later issue to add explicit lifecycle metadata and a data-preserving transition before removing the declaration.

The database stores the package's permission and assignment records. Code and translation dictionaries remain the source for names shown in Admin. This prevents the database or UI from inventing an ability that no route, Gate, or policy enforces.

Permission labels live in the Authorization module's namespaced server translations. They are not part of the globally shared translation dictionaries; an authorized Admin response may resolve and expose only its allowlisted page catalog.

## Module integration checklist

A module that adds a protected capability must change all applicable layers in one issue and PR:

1. Add or extend the module's permission enum.
2. Register the enum with the application Authorization catalog through the module service provider.
3. Add or extend the model policy, Gate, route `can` middleware, controller authorization, or Form Request authorization.
4. Put reusable authorization and mutation rules in the owning service/action, not in React or scattered role checks.
5. Add the English and Malay permission label/description keys used by Admin.
6. Expose only page-relevant capability booleans or an allowlisted ability map through Inertia.
7. Add focused allow and deny tests for every affected role, plus owner/non-owner tests for addressed records.
8. Add activity events for privileged mutations with allowlisted metadata.
9. Update this reference, the web interface catalog, and operations documentation when their contracts change.
10. Run focused tests, `composer check`, locked audits, and a security diff review.

Registering a permission does not automatically approve it for every role. Protected role mappings live in the Authorization module and custom mappings remain Admin-managed after #134. New control-plane permissions may be added to `super-admin` only through the reviewed catalog mapping; they never bypass domain policies.

## Synchronization and drift

The Authorization module owns an idempotent catalog synchronizer. It may:

- create missing declared permission rows for the `web` guard;
- update approved package-owned lookup state;
- create missing protected roles;
- synchronize reviewed protected-role mappings; and
- clear the package permission cache after a successful transaction.

It must not silently delete an unknown permission, role, mapping, or user assignment. A row can be unknown during rollback, a mixed-version deployment, or recovery from older data. The synchronizer reports drift and leaves destructive resolution to an explicit, reviewed operation.

Use package mutation APIs rather than direct table writes. Direct writes can leave the package cache stale and bypass assignment invariants.

Run `php artisan authorization:sync` after migrations and before enabling authorization-dependent routes. The command creates missing declared rows and mappings, clears the permission cache, and records `authorization.catalog_synchronized`. It returns a failure status when unknown roles, permissions, mappings, or direct user permissions require operator review; it does not delete that data.

The package's universal permission callback is disabled. The Authorization provider registers only catalog-declared permission names with Laravel Gate, preventing an unknown generic database permission such as `view` or `delete` from bypassing a model policy with the same ability name.

## Inertia contract

Shared props contain only capabilities needed by global navigation, such as whether the current user may enter Admin. Page-specific Admin responses may expose an allowlisted permission catalog after server authorization.

Never expose:

- numeric user, role, or permission keys;
- package models or pivot records;
- the complete permission catalog on ordinary pages;
- credentials, sessions, reset tokens, or API tokens;
- private Planning values for administrative convenience; or
- a role name as proof that a submitted request is authorized.

Keep PHP data objects and TypeScript types aligned when capability props change.

## Super-admin boundary

Expenses App does not use a universal `Gate::before` rule that returns `true` for every super-admin ability. That pattern would also bypass present and future model policies unless each exception were reconstructed elsewhere.

Instead, the protected `super-admin` role receives explicit control-plane permissions. Laravel evaluates those permissions through its normal Gate path. Planning and other private domain policies still require ownership. A future support-access or impersonation feature needs a separate permission, purpose limitation, confirmation, audit trail, data contract, and issue.

## Activity events

Authorization work extends the ActivityLog allowlist with narrowly named events as the related issues deliver them. Expected event families include catalog synchronization, role lifecycle, role assignment, permission mapping, and super-admin lifecycle.

Store public actor/subject identifiers and stable role or permission names only when the event contract needs them. Never store passwords, tokens, sessions, emails, request bodies, IP addresses, user agents, internal database IDs, exception messages, or Planning values. Privileged mutations and their activity rows share one transaction where the current activity contract requires atomic history.

## Testing contract

Every authorization change needs focused coverage for its applicable cases:

- guest and unverified denial;
- authenticated user allow/deny;
- admin and super-admin allow/deny;
- owner and non-owner record access;
- missing, unknown, and retired permissions;
- protected-role invariants;
- direct-request denial when React hides an action;
- idempotent synchronization and catalog drift;
- cache invalidation after role/permission changes;
- transaction rollback when activity capture fails;
- migration up/down with representative existing users; and
- public Inertia props without internal authorization IDs.

Use isolated SQLite for the normal Pest suite and a separate disposable PostgreSQL configuration for PostgreSQL-specific schema behavior. The exact PR/release head must pass `composer check`.

## Deferred extensions

The contract leaves room for billing modules to register abilities such as `billing.view` or `billing.settings.manage`, but v2.2.0 does not implement billing. Organizations, workspaces, tenant-scoped roles, team-aware package configuration, API guards, wildcard permissions, direct user permissions, and impersonation remain separate decisions.

A future tenancy design must define which account owns a role assignment and how cross-tenant cache keys, queries, policies, migrations, and recovery work. Do not enable the package's teams feature before that decision.

## Implementation status

| Capability | Status/source |
| --- | --- |
| Planning owner policy | Implemented in `PlanningPolicy` |
| Database-backed roles/permissions | Implemented by #13 with `spatie/laravel-permission` 8.3.0 |
| Catalog synchronization and drift reporting | Implemented by #13 through `authorization:sync` |
| Planning capability plus ownership policies | Implemented by #13 |
| Shared Admin capability boolean | Implemented by #13; no Admin routes yet |
| Super-admin lifecycle | Planned by #132 |
| Admin shell | Planned by #40 |
| User-role administration | Planned by #133 |
| Role-permission administration | Planned by #134 |
| Operations guide and integrated readiness | Planned by #135 |
