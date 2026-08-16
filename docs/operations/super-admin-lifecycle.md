# Super-admin lifecycle operations

This runbook is for an infrastructure operator authorized to bootstrap, rotate, or recover Expenses App platform access. It covers the server-side lifecycle implemented by issue [#132](https://github.com/lurzar/expenses-app/issues/132). It does not create accounts, reset passwords, grant access to private Planning data, or replace the deployment platform's shell-access audit.

## Security boundary

- The target must already exist, be email-verified, and not be soft-deleted.
- Select accounts by public `user_id` ULID only. The command does not accept an email address or internal numeric ID.
- No migration, seeder, environment variable, or deployment step creates a default super-admin or credential.
- `super-admin` receives explicit control-plane permissions and never bypasses Planning ownership policies.
- Activity capture must be enabled and available. A lifecycle mutation and its minimized activity event share one transaction.
- Grant, removal, rotation, permitted deletion, and permitted unverification revoke existing authorization sessions by incrementing `authorization_version` and rotating the Laravel remember token.
- The final verified, non-deleted super-admin cannot lose the role, delete the account, or become unverified until another active operator exists.

## Prerequisites

1. Confirm the exact deployed revision and maintenance/change approval.
2. Back up `users`, `roles`, `model_has_roles`, and `activity_logs` according to the deployment's data policy.
3. Verify `ACTIVITY_LOG_ENABLED=true` and that the activity-log migration is present.
4. Run migrations before the command, then synchronize the protected catalog:

   ```sh
   php artisan migrate --force
   php artisan authorization:sync
   ```

5. Resolve the target's public ULID through an approved account-support or read-only database process. Do not paste the account email, password, session, or database row into an issue, PR, chat, or command history.
6. Confirm control of the target account and complete any required password recovery before granting privilege. Existing sessions will be logged out, so a fresh login is required afterward.

Replace `php artisan` with `./vendor/bin/sail artisan` when the deployment runs commands through Laravel Sail.

## Initial bootstrap or recovery

Run the command without `--force` so the target public identifier is confirmed interactively:

```sh
php artisan authorization:super-admin 01EXAMPLEPUBLICULID00000000
```

Expected behavior:

1. The command displays only the public ULID and asks for confirmation.
2. Laravel locks the protected role and target account inside a database transaction.
3. An ineligible, missing, or malformed selection fails without a role or activity change.
4. A repeated grant reports that access already exists and does not add another event or revoke sessions again.
5. A successful grant records `authorization.super_admin_granted` with a system (`null`) actor and the target public ULID.
6. Existing browser and remember-me sessions become stale; the operator signs in again through the normal login page.

Use `--force` only for an approved non-interactive deployment job whose execution identity and change record are captured outside the application:

```sh
php artisan authorization:super-admin 01EXAMPLEPUBLICULID00000000 --force
```

## Rotation

Rotation assigns the replacement before removing the previous role in the same transaction:

```sh
php artisan authorization:super-admin 01REPLACEMENTPUBLICULID000 \
    --replace=01PREVIOUSPUBLICULID000000
```

The replacement must be verified and active. The previous account must still hold `super-admin`; it may be unverified or soft-deleted during controlled recovery. A successful rotation records one `authorization.super_admin_rotated` event whose subject is the replacement and whose only metadata is the previous account public ULID. Both accounts' existing sessions are revoked.

After rotation:

1. Sign in as the replacement account in a fresh browser session.
2. Verify `admin.access` through the normal application authorization path. Do not test by querying another user's Planning data.
3. Confirm the previous account no longer has `super-admin` and cannot enter the control plane.
4. Confirm the minimized rotation event exists without retrieving unrelated activity or account data.

## Final-operator protection

Application role removal delegates to `SuperAdminLifecycleService`. Protected deletion and verified-to-unverified transitions also run through its audited active-status transaction. If only one verified, non-deleted super-admin remains, unverification and deletion of that active operator fail before changing account, session, or activity state. When another active operator exists, the transition requires activity capture and revokes the affected account's browser and remember-me sessions in the same transaction. Deleting an already-unverified super-admin also revokes sessions before removal so restoration cannot revive retained access.

The user observer rejects direct Eloquent deletion or unverification of a super-admin outside the lifecycle-owned transaction. Future Admin features must call the lifecycle service; wrapping `save()` or `delete()` in a caller-owned transaction is not a substitute for its locking, revocation, and activity contract.

Do not bypass the invariant with direct pivot-table SQL, `DB::table()` writes, manual role deletion, disabled Eloquent events, or package APIs outside the owning service. If access is already lost, restore database/application availability and use the documented command against an eligible existing account; do not create a credential in source or configuration.

## Failure handling

- **Catalog unavailable:** run `authorization:sync`, review all reported drift, and retry only after the protected `super-admin` role is present.
- **Activity unavailable or disabled:** restore the activity table/configuration first. The lifecycle operation deliberately fails closed.
- **Target ineligible:** verify that the selected account exists, is not deleted, and has completed email verification. The command does not reveal which condition failed.
- **Rotation failure:** the transaction retains the previous role, replacement role, session versions, remember tokens, and activity state as they were before the attempt.
- **Stale browser session:** sign in again. Do not copy authorization versions or session values between clients.

## Rollback and recovery

The migration adds only `users.authorization_version`; it does not move or delete users, roles, assignments, or Planning data. Roll application code back before dropping the column. Existing role assignments remain authoritative in the earlier revision.

For a deployment rollback:

1. Stop privileged mutations and preserve an approved backup.
2. Stop web traffic and workers, identify the configured production session driver, and invalidate **all** application sessions using that store's approved purge procedure. A normal cache clear is not proof that database, Redis, file, cookie, or another session store was purged.
3. Invalidate persistent-login tokens for all accounts through the deployment's approved bulk-logout procedure. Record the procedure and result in the change log without copying tokens or account data.
4. Verify that a browser cookie captured before the purge redirects to login. Do not continue if any old session still authenticates.
5. Deploy the prior application revision so it no longer references the authorization-session middleware or column.
6. Roll back only `2026_08_16_110000_add_authorization_version_to_users.php` using the deployment's reviewed migration procedure.
7. Do not remove `super-admin` assignments or drop the authorization/activity tables as part of this rollback.
8. Before restoring #132, repeat the complete session-store and persistent-login invalidation. Reapplying the migration initializes `authorization_version` to zero, so retaining an old version-zero session would revive it.
9. Verify the pre-purge cookie remains rejected, then verify normal login, Planning ownership, the known active operator assignment, and the selected revision's full quality gate before resuming traffic.

To restore #132, deploy its application revision, run migrations and `authorization:sync`, then complete a fresh operator login and a non-sensitive lifecycle smoke check.

## Verification

Use an isolated database only:

```sh
php artisan test tests/Feature/Authorization/SuperAdminLifecycleTest.php
php artisan authorization:super-admin --help
composer check
```

The focused suite covers verified bootstrap, idempotence, confirmation, invalid targets, mandatory auditing, atomic rotation and rollback, last-active-operator protection, session revocation, seeder separation, catalog failure, and migration up/down behavior.
