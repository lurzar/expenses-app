# Activity logging operations

This guide helps an authorized operator inspect and manage the durable activity history introduced in version 2.0.8. It describes server-side operations only. The application has no activity-log page, public API, role-authorized viewer, alerting integration, or analytics interface.

## Purpose and boundaries

Activity logging answers a narrow question: when did an approved account or Planning state change occur, and which public identifiers were involved? It does not store a snapshot of the changed record.

Activity history differs from Telescope and application logs:

| Source | Purpose | Data policy |
| --- | --- | --- |
| Activity log | Durable history of approved state changes | Public ULIDs and allowlisted metadata only; retained for 365 days by default |
| Telescope | Short-lived Laravel diagnostics for authorized operators | May contain request, query, exception, and runtime details |
| Application log | Runtime messages and failures | Must not serve as the durable activity history |

The Activity Log module owns the model, recorder, migration, pruning command, and event catalog under `app/Modules/ActivityLog/`. Capture points remain with the modules that own each mutation.

## Recorded events

The application records these events:

| Event | Capture point | Actor | Subject | Metadata |
| --- | --- | --- | --- | --- |
| `account.registered` | Successful account creation | New account public ULID | `account` and the same public ULID | None |
| `account.profile_updated` | Successful name or email change | Account public ULID | `account` and the same public ULID | `changed_fields` containing `name`, `email`, or both |
| `account.deleted` | Successful account soft deletion | Account public ULID | `account` and the same public ULID | None |
| `planning.created` | Successful Planning creation | Account public ULID | `planning` and the Planning public ULID | None |
| `planning.deleted` | Successful Planning soft deletion | Account public ULID | `planning` and the Planning public ULID | None |
| `authorization.catalog_synchronized` | Catalog synchronization transaction | System (`null`) | `authorization_catalog` with no invented identifier | Created permission/role/mapping names and drift identifiers |
| `authorization.custom_role_created` | Custom-role creation transaction | Operator account public ULID | `authorization_role` with no invented identifier | Stable custom role name and approved permission names |
| `authorization.custom_role_updated` | Custom-role update transaction | Operator account public ULID | `authorization_role` with no invented identifier | Before/after stable role and permission names |
| `authorization.custom_role_retired` | Custom-role retirement transaction | Operator account public ULID | `authorization_role` with no invented identifier | Stable custom role name and approved permission names |
| `authorization.role_assigned` | Role assignment service transaction | Operator account public ULID | Target `account` public ULID | Stable role name |
| `authorization.role_removed` | Role removal service transaction | Operator account public ULID | Target `account` public ULID | Stable role name |
| `authorization.super_admin_granted` | Super-admin lifecycle transaction | Operator account public ULID or system (`null`) | Target `account` public ULID | None |
| `authorization.super_admin_removed` | Super-admin lifecycle transaction | Operator account public ULID or system (`null`) | Target `account` public ULID | None |
| `authorization.super_admin_rotated` | Atomic super-admin rotation | Operator account public ULID or system (`null`) | Replacement `account` public ULID | Previous account public ULID only |

Catalog synchronization is system-generated and therefore uses a null actor and subject identifier. It does not invent a user or a public ULID. Account and Planning events retain public actor and subject identifiers.

The `authorization:super-admin` command records a null actor because Laravel does not authenticate a browser user for an Artisan process. The event name records what initiated the change; the approved deployment or shell-access audit must identify the human operator. The command never accepts or records an email, password, token, session value, or internal key.

A verified super-admin may become unverified only when another active operator remains; verified or already-unverified super-admin deletion always uses the audited lifecycle. The lifecycle-owned transaction records the existing minimized `account.profile_updated` or `account.deleted` event and rolls back the account and session-revocation changes if capture fails. The user observer rejects direct Eloquent deletion or unverification outside that context. Future administrative account-management paths must reuse the same lifecycle transaction rather than saving or deleting the model directly.

Account deletion produces one `account.deleted` event. The account observer soft-deletes the account's Planning records without emitting a `planning.deleted` event for each record. Activity rows have no foreign keys to accounts or Planning records, so the public actor and subject identifiers remain available after soft deletion.

Each capture runs in the same database transaction as its state change. If enabled capture fails, Laravel rolls back both the mutation and its activity row. Failed validation and a profile update that changes neither name nor email produce no event.

Admin user-role changes record one existing assignment/removal event per changed role. Repeated desired-state submissions that make no change produce no event. Each changed assignment also increments the subject's authorization revision and rotates its remember token so retained sessions cannot continue with stale privileges. Custom-role mapping changes record one allowlisted before/after event and revoke every assigned account's sessions before the updated access can be used.

Issue [#13](https://github.com/lurzar/expenses-app/issues/13) provides the authorization foundation, but it does not add an activity-log viewer. Any future viewer needs its own permission, access review, data contract, tests, and issue.

## Stored fields and prohibited data

The `activity_logs` table stores:

- an internal database key;
- a public `activity_id` ULID;
- the approved event name;
- an optional public actor ULID;
- the subject type and public subject ULID;
- allowlisted JSON metadata; and
- `created_at`.

The application model rejects updates and application-level deletion. The retention command performs the only supported application deletion path.

Never add these values to an activity record:

- names, email addresses, passwords, reset tokens, session values, or credentials;
- salary, totals, allocations, categories, or other financial values;
- request bodies, headers, cookies, IP addresses, user agents, or raw diagnostics;
- model snapshots, exception messages, free-form notes, or arbitrary metadata;
- internal numeric account or Planning database keys.

Profile metadata records field names only. For example, `{"changed_fields":["email"]}` says that the email field changed; it does not record either email value.

## Configuration and deployment

Set these values in the deployment environment:

```dotenv
ACTIVITY_LOG_ENABLED=true
ACTIVITY_LOG_RETENTION_DAYS=365
```

`ACTIVITY_LOG_ENABLED` accepts a valid boolean. A malformed value disables capture. `ACTIVITY_LOG_RETENTION_DAYS` must be a positive integer; an absent or malformed value uses 365 days.

Run the migration before enabling code that can capture an event:

```sh
php artisan migrate --force
php artisan authorization:sync
php artisan config:cache
php artisan schedule:list
```

Confirm that `schedule:list` contains `activity-log:prune --days=365` with a daily frequency. Replace `php artisan` with `./vendor/bin/sail artisan` when the application runs through Laravel Sail.

Authorization schema rollback permits only the deterministic bootstrap state that can be reconstructed. It stops before dropping tables when non-default role assignments, direct permissions, unknown catalog rows, unexpected mappings, or orphan assignments exist. Back up and explicitly resolve that state before retrying; never bypass the guard by dropping tables manually.

## Safe inspection

Only an operator with approved, read-only database access may inspect activity history. Application authentication does not grant this access, and the repository defines no administrator role for it.

Use an encrypted database connection and select only the activity table's approved columns. Do not join activity rows to accounts or Planning records merely to reveal names, email addresses, or financial values.

```sql
SELECT activity_id,
       event,
       actor_id,
       subject_type,
       subject_id,
       metadata,
       created_at
FROM activity_logs
ORDER BY created_at DESC
LIMIT 100;
```

Filter by an already approved public ULID, event name, or time range when investigating one change. Keep query results in the approved operational system; do not paste them into issues, pull requests, chat, documentation, or application logs.

Monitor storage without retrieving activity details:

```sql
SELECT count(*) AS entries,
       min(created_at) AS oldest_entry,
       max(created_at) AS newest_entry,
       pg_size_pretty(pg_total_relation_size('activity_logs')) AS total_size
FROM activity_logs;
```

Investigate unexpected growth by checking scheduler health, the configured retention period, and event counts. Do not sample raw account or financial records as part of that check.

## Retention and pruning

Laravel schedules pruning once per day without overlap. The command deletes rows older than the configured number of days:

```sh
php artisan activity-log:prune
```

An operator may supply a temporary positive retention period:

```sh
php artisan activity-log:prune --days=365
```

The command rejects zero, negative, and non-integer values before deleting any row. Pruning is irreversible unless a separate approved backup exists. Confirm the retention requirement and target environment before running it manually.

Disabling capture does not disable scheduled pruning. Existing rows continue to age out under the configured retention policy.

## Disable capture

Disable capture when the activity table is unavailable or a suspected data-policy violation requires containment:

1. Set `ACTIVITY_LOG_ENABLED=false` in the target environment.
2. Refresh the deployed configuration with `php artisan config:cache`.
3. Confirm the effective value with `php artisan config:show activity-log` on the target server.
4. Verify that an approved non-authorization test mutation succeeds without adding an activity row.
5. Record the start and end of the audit gap in the incident or deployment record without including personal or financial data.

While capture is disabled, ordinary account and Planning mutations continue without durable activity events. Authorization role changes and protected super-admin lifecycle mutations fail closed because those security-sensitive operations require atomic durable history. Re-enable capture only after the migration is present and the recorder path passes its focused tests.

## Respond to suspected exposure

If an activity row may contain prohibited data:

1. Disable capture to contain further writes.
2. Restrict database and backup access to the incident team.
3. Preserve the minimum evidence required to identify the event, affected fields, time range, and introducing revision. Do not copy the exposed value into the incident record.
4. Determine whether the value is a credential or secret. If so, assess rotation or revocation immediately; pruning does not invalidate a disclosed secret.
5. Obtain the data owner's approval before deleting or pruning evidence. The application provides age-based pruning only; a selective purge requires a separately reviewed database operation.
6. Fix the capture contract and add a regression test that uses synthetic values.
7. Validate the fix in an isolated database, deploy it, run pruning or the approved selective purge, and confirm that retained rows follow the allowlist.
8. Re-enable capture and document the audit gap and follow-up actions.

Also assess database replicas, logical backups, exported query results, and diagnostic systems. Removing the primary row does not remove a copy held elsewhere.

## Rollback

Dropping the migration deletes all activity rows. Treat that action as destructive and obtain the data owner's approval first.

1. Set `ACTIVITY_LOG_ENABLED=false` and refresh the configuration.
2. Drain or pause state-changing traffic.
3. Check `php artisan migrate:status` and confirm the exact activity migration and any newer migrations.
4. Preserve an approved backup if policy requires the activity history.
5. Roll back only the activity migration with the deployment's reviewed migration procedure. Do not use a broad rollback when newer migrations exist.
6. Deploy the matching earlier application revision so the scheduler and mutation paths no longer reference the Activity Log module.
7. Resume traffic and verify registration, profile changes, account deletion, and Planning create/delete behavior.

To restore the feature, deploy the activity-logging code, run `php artisan migrate --force`, confirm the table and scheduler, then set `ACTIVITY_LOG_ENABLED=true` and refresh the configuration. Never enable capture before the migration succeeds.

## Verification

Use an isolated test database for application checks:

```sh
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test tests/Feature/System/ActivityLoggingTest.php
php artisan activity-log:prune --help
php artisan schedule:list
```

The focused test covers the event catalog, data minimization, transaction rollback, disabled capture, append-only model behavior, retention, pruning, and scheduling.
