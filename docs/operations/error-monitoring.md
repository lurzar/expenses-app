# Error monitoring and incident response

This how-to guide is for authorized Expenses App operators who need to inspect Laravel Telescope diagnostics, control retention, or respond to suspected diagnostic-data exposure. It documents the behavior implemented in [issue #36](https://github.com/lurzar/expenses-app/issues/36).

> Telescope can contain personal data, application internals, queries, mail metadata, and stack traces. Use it only for a specific investigation. Never copy raw entries into issues, pull requests, chat, or documentation.

## Implementation map

Use the source as the authority when this guide and the application disagree:

- [`.env.example`](../../.env.example) lists the supported environment variables.
- [`config/telescope.php`](../../config/telescope.php) defines Telescope storage, paths, watchers, and validated defaults.
- [`app/Providers/AppServiceProvider.php`](../../app/Providers/AppServiceProvider.php) controls provider registration.
- [`app/Providers/TelescopeServiceProvider.php`](../../app/Providers/TelescopeServiceProvider.php) defines access, filtering, and redaction.
- [`routes/console.php`](../../routes/console.php) defines scheduled pruning.
- [`tests/Feature/System/TelescopeSecurityTest.php`](../../tests/Feature/System/TelescopeSecurityTest.php) verifies the security boundary.

Activity/audit logging from [issue #34](https://github.com/lurzar/expenses-app/issues/34), role-based access, external alerting, and monitoring-vendor integrations are not implemented.

## Understand the environment boundary

In the `local` environment, the application registers Telescope even when `TELESCOPE_ENABLED` is false. Telescope then accepts entries allowed by the enabled watchers in `config/telescope.php`. This behavior supports local development only.

In every non-local environment, the application registers Telescope only when `TELESCOPE_ENABLED` resolves to the boolean value `true`. Missing, false, or malformed values disable it. A malformed retention value falls back to 168 hours.

Non-local filtering retains only:

- reportable exceptions;
- failed requests and jobs;
- scheduled-task entries;
- `error`, `critical`, `alert`, and `emergency` logs; and
- entries with a monitored tag.

Routine successful requests, debug logs, and other unmonitored noise are discarded. Enabled watchers may still collect sensitive application context before filtering, so application code must never put credentials into logs, exception messages, monitored tags, queries, or diagnostic strings.

## Enable non-local diagnostics

1. Select the smallest set of verified operator accounts needed for the investigation.
2. Set these values through the environment-management process for the target deployment:

   ```dotenv
   TELESCOPE_ENABLED=true
   TELESCOPE_AUTHORIZED_EMAILS=operator@example.test
   TELESCOPE_PRUNE_HOURS=168
   ```

   `TELESCOPE_AUTHORIZED_EMAILS` accepts a comma-separated list. Every item must be a valid email address. An empty value or one malformed item denies access to everyone.

3. Refresh the application's configuration using the deployment's normal configuration-cache procedure. For an uncached local verification environment, run:

   ```bash
   php artisan config:clear
   ```

4. Confirm that Telescope routes and daily pruning are registered:

   ```bash
   php artisan route:list --name=telescope
   php artisan schedule:list
   ```

5. Sign in with an allowlisted account whose email is verified, then open `/telescope`. Guests, unverified users, and users outside the allowlist receive `403 Forbidden`.

Do not enable Telescope until the allowlist and scheduled pruning are both verified.

## Inspect an incident safely

1. Record the user-visible symptom, approximate time window, affected feature, and a non-sensitive correlation value such as an internal request identifier.
2. Open Telescope with a verified, allowlisted account.
3. Search the narrowest time window and entry category that matches the symptom.
4. Start with the failed request, exception, failed job, or error-level log. Follow related entries only when needed.
5. Record conclusions and sanitized identifiers. Paraphrase error details; never export a raw payload, stack trace, query, mail body, cookie, token, or user record.
6. Apply the smallest application fix, then reproduce the original operation in a safe environment.
7. Confirm the user-visible behavior and relevant automated checks before closing the incident.
8. Remove temporary access and disable non-local Telescope when the investigation ends.

Request parameters and headers with known credential keys are hidden. Retained request and log structures also receive recursive credential-key redaction. If a retained log context contains a credential field, Telescope replaces the rendered message because interpolation may have copied the value. Safe diagnostic fields remain available.

Redaction reduces risk; it does not make Telescope an approved store for secrets. Arbitrary credentials embedded in free-form strings or unexpected data shapes cannot be classified reliably.

## Prune diagnostic data

The scheduler runs daily and retains the configured number of hours. It uses a lock to prevent overlapping prune jobs. Confirm the scheduler itself runs through the deployment's existing scheduler process; this repository does not define or deploy that infrastructure.

To run the documented retention policy manually, execute:

```bash
php artisan telescope:prune --hours=168
```

Replace `168` only with an approved positive retention value. Pruning permanently deletes older Telescope entries. Confirm the target environment and retention window before running the command.

During a confirmed diagnostic-data exposure, an incident owner may approve a full Telescope purge:

```bash
php artisan telescope:prune --hours=0
```

Run the purge before disabling the provider, or use an approved one-command configuration override. Treat this as destructive: preserve only sanitized incident evidence required by policy, and never copy the exposed raw data elsewhere.

## Disable or roll back Telescope

1. Set `TELESCOPE_ENABLED=false` in the target environment.
2. Refresh the application's configuration cache through the deployment's normal procedure.
3. Run `php artisan route:list --name=telescope`. The application must expose no Laravel Telescope routes. An unrelated Debugbar route named `debugbar.telescope` may appear in development tooling.
4. Run `php artisan schedule:list` and confirm the Telescope prune schedule is absent outside `local`.
5. Revoke any temporary operational access granted for the investigation.

Disabling Telescope stops new application registration and capture; it does not delete existing database entries. Prune retained entries separately when policy or an incident requires deletion.

To roll back the code change, revert the Telescope implementation pull request through the normal release process and leave `TELESCOPE_ENABLED=false`. Re-enable it only after the focused Telescope security tests and access checks pass.

## Respond to suspected exposure

1. Contain capture by disabling Telescope, unless a full purge must run first.
2. Restrict application and database access to the incident responders.
3. Determine the affected time window, entry types, users, environments, and credential classes without reproducing raw values.
4. Rotate a credential when a Telescope entry may contain its value, when exposure cannot be ruled out, or when an unauthorized person could access the diagnostic store. This includes session cookies, API tokens, authorization headers, CSRF tokens, passwords, and infrastructure credentials.
5. Invalidate active sessions or tokens when their secrets may be exposed.
6. Prune the affected Telescope data with incident-owner approval.
7. Validate that Telescope routes are absent, capture has stopped, and the affected credentials no longer work.
8. Fix the source that logged or attached the sensitive value. Add a regression test when the application boundary should have removed it.
9. Record a sanitized incident summary, decisions, rotations, validation, and follow-up issue links.
10. Re-enable non-local diagnostics only after security review and operator-access verification.

If the implementation behavior is uncertain, keep Telescope disabled, correct this canonical guide and the related issue, and repeat the security checks before restoring access.
