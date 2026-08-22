# Authorization and Admin operations

This runbook is for an approved platform operator maintaining the authorization and Admin control plane delivered through issues [#13](https://github.com/lurzar/expenses-app/issues/13), [#132](https://github.com/lurzar/expenses-app/issues/132), [#133](https://github.com/lurzar/expenses-app/issues/133), and [#134](https://github.com/lurzar/expenses-app/issues/134). It becomes canonical when [#135](https://github.com/lurzar/expenses-app/issues/135) merges.

It does not provide access to private Planning data, create credentials, grant direct user permissions, or authorize direct database/pivot-table changes.

## Operating model

| Role | Explicit control-plane capabilities | Operator boundary |
| --- | --- | --- |
| `user` | None | Mandatory base role; manages only owned Planning data through policies. |
| `admin` | `admin.access`, `users.view`, `users.manage-roles` | May manage `admin` membership for other eligible accounts. |
| `super-admin` | Admin capabilities plus `users.manage-super-admin`, `roles.manage` | May perform protected super-admin lifecycle changes and manage custom role mappings. |

Roles are not a universal bypass. Laravel Gate, route middleware, Form Requests, and record policies remain authoritative. In particular, neither Admin nor super-admin access permits viewing or changing another account's Planning data.

`user`, `admin`, and `super-admin` are protected code-owned roles. Only custom roles may be created, renamed, mapped, or retired through `/admin/roles`. The permission catalog is code-owned; the Admin interface cannot create a permission name that no module has declared and enforced.

## Deployment and verification

Before enabling a release that changes authorization:

1. Record the deployed revision and approved maintenance window.
2. Back up `users`, `roles`, `permissions`, role/permission pivots, and `activity_logs` using the deployment's approved process.
3. Confirm activity logging is enabled; privileged mutations fail closed without it.
4. Deploy the application revision, run migrations, then synchronize the declared catalog:

   ```sh
   php artisan migrate --force
   php artisan authorization:sync
   php artisan config:cache
   ```

5. Treat synchronization drift as a stop condition. Unknown roles, permissions, mappings, direct user permissions, or orphan assignments are preserved and reported; do not delete them with direct SQL.
6. Sign in with a known verified active operator account in a fresh session. Confirm only the expected Admin destinations are shown and direct requests are still denied for an ordinary account.
7. Record the exact-head `composer check` result in the change record. Use a disposable database for automated tests.

Use `./vendor/bin/sail artisan` instead of `php artisan` when the deployment runs commands through Laravel Sail. Super-admin bootstrap, rotation, recovery, and last-operator protection remain in the [super-admin lifecycle runbook](super-admin-lifecycle.md).

## User administration

`/admin/users` deliberately exposes only public user ULIDs, name, email, verification state, administrative memberships, and an authorization revision. It does not expose numeric keys, credentials, sessions, private financial data, or package models.

- Granting `admin` or `super-admin` requires a verified, active account.
- The base `user` role is not editable in Admin.
- Operators cannot remove their own administrative access using this interface.
- `super-admin` changes require the protected lifecycle and the `users.manage-super-admin` capability.
- Repeated desired-state requests are safe; changed stale revisions fail without mutation.

After a successful role change, the affected account's authorization version and remember token rotate. The account must sign in again before relying on its new access. Do not attempt to repair a stale session by copying browser cookies, session values, or authorization versions.

## Custom role administration

Only `super-admin` can open `/admin/roles` or mutate custom roles.

- Create names are normalized, unique, and cannot impersonate a protected role.
- Map only choices offered by the approved, module-owned catalog.
- Resolve an unknown/retired permission through a reviewed deployment or catalog migration; the interface surfaces it rather than silently removing access.
- A role with assigned users cannot be retired. Reassign or remove every assignment through the approved user-management workflow first.
- Changing a custom role revokes sessions for all assigned accounts, clears the permission cache, and records before/after role metadata.

Do not use direct database writes, package mutation APIs outside the owning service, disabled model events, or manual cache-table edits. They can bypass protected-role, session-revocation, and audit invariants.

## Audit and troubleshooting

Review only the minimum approved activity history necessary for the change. Relevant event names are `authorization.catalog_synchronized`, `authorization.role_assigned`, `authorization.role_removed`, `authorization.custom_role_created`, `authorization.custom_role_updated`, `authorization.custom_role_retired`, `authorization.super_admin_granted`, `authorization.super_admin_removed`, and `authorization.super_admin_rotated`.

Activity metadata contains only stable public identifiers and allowlisted role/permission names. Do not add account emails, passwords, session values, IP addresses, request bodies, internal database keys, or Planning values to a ticket or change record.

- If an expected Admin link is absent, verify the account is verified/active, has the required code-owned capability, and has signed in after the latest authorization change.
- If access persists after removal, confirm the mutation succeeded and reauthenticate. Clear the configured permission cache only through the approved deployment process; do not delete cache rows manually.
- If `authorization:sync` reports drift, preserve the rows, identify the owning release, back up, and resolve through a reviewed migration or recovery plan.
- If role retirement is blocked, resolve assignments first; never delete role/pivot rows directly.

## Rollback and recovery

Stop privileged mutations and preserve a verified backup before rollback. Roll application code back before rolling back an authorization migration. The role-management rollback refuses to discard direct or non-super-admin `roles.manage` mappings; the user-administration rollback similarly refuses unsafe assignments. These guards are expected safety controls, not errors to bypass.

1. Preserve audit history and role/permission/assignment rows.
2. Invalidate application sessions and persistent-login tokens with the deployment's approved procedure.
3. Deploy the prior application revision and run only its reviewed migration rollback sequence.
4. Run `authorization:sync` for the restored revision; resolve drift from backup/audit evidence rather than deleting it blindly.
5. Verify fresh login, a known operator path, Planning ownership denial for a different account, and the revision's full quality gate before resuming traffic.

Never drop authorization or activity tables as a shortcut. The detailed super-admin session rollback procedure is authoritative for `authorization_version` recovery.
