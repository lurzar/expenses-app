# External integrations

This reference distinguishes configured capabilities from integrations that application source actually uses. The current product has no public REST API and no application-owned outbound business API call.

## Integration inventory

| System | Type | Purpose | Authentication/config | Criticality | Evidence |
| --- | --- | --- | --- | --- | --- |
| PostgreSQL 18.4 | Database | Users, monthly Planning, framework tables, activity history, Telescope | Environment credentials | High | `.env.example`; `docker-compose.yml`; migrations |
| Laravel file cache | Local cache | Default Planning collection cache | Filesystem permissions | Medium | `config/cache.php`; `.env.example` |
| Redis 8.8.1 | Optional cache/data service | Alternative Laravel cache/Redis connections | Optional environment password | Medium when enabled | `docker-compose.yml`; `config/database.php` |
| pgAdmin 9.16 | Development administration UI | Inspect local PostgreSQL | Development email/password variables | Low; development only | `docker-compose.yml`; `.env.example` |
| SMTP/Mailpit host | Mail transport configuration | Verification and password-reset mail | SMTP environment variables | Medium | `.env.example`; `config/mail.php` |
| Inertia + Ziggy | Browser/server boundary | Server-driven page contracts and named URLs | Session auth + CSRF | High | `HandleInertiaRequests.php`; `app.tsx`; `route.ts` |
| Telescope | Conditional diagnostics | Request/query/log/failure diagnostics and pruning | Explicit enablement plus verified email allowlist outside local | Medium; operational | `AppServiceProvider.php`; `TelescopeServiceProvider.php` |
| Debugbar | Local diagnostics | Development request inspection | Local environment/debug configuration | Low; development only | `composer.lock`; Laravel integration |
| Laravel Sanctum | Installed auth capability | `User` token trait and framework token table | Token model if later used | Low currently | `User.php`; Sanctum migration/config |

Sanctum's presence does not mean a public API exists. The route inventory has 28 application web routes and no `routes/api.php` or application API controller/resource.

## Data stores

| Store | Role | Access layer | Key risk | Evidence |
| --- | --- | --- | --- | --- |
| PostgreSQL | Authoritative relational data | Eloquent, migrations, Laravel DB transactions | Fixed-precision Planning migration and partial unique index require PostgreSQL-aware deployment review | Planning migration/model/service/ADR 0001 |
| File cache | Default collection cache | Laravel cache repository through `PlanningCache` | Shared filesystem/permission behavior differs across deployments | `config/cache.php`; `PlanningCache.php` |
| Redis | Optional cache connection | Laravel cache/Redis configuration | Operational behavior depends on selected store and persistence | `config/cache.php`; `config/database.php` |
| Filesystem storage | Logs, sessions, local diagnostics, generated/runtime files | Laravel filesystem/session/logging | Runtime artifacts may contain sensitive data and must remain untracked | `config/filesystems.php`; `.gitignore`; operations docs |

## Secrets and credentials

- Credential names and safe local defaults are declared in `.env.example`; real `.env` files are ignored.
- Laravel configuration reads database, Redis, mail, AWS, Telescope, pgAdmin, and application secrets from the environment.
- No committed application source contains a production credential identified by the source/config scan.
- Telescope redaction and activity metadata minimization are documented under `docs/operations/`.
- `[ASK USER]` The production secret store, rotation schedule, database backup owner, and restore operator are not defined in committed source.

## Reliability and failure behavior

- Planning/account/profile mutations use database transactions when activity history must remain atomic.
- Planning collection cache invalidation happens only after a successful transaction.
- Cache failures use Laravel's selected repository behavior; no application-specific retry/backoff is implemented.
- No application-owned outbound HTTP retry, timeout, or circuit-breaker policy was found because no business API client is implemented.
- Queue execution is synchronous in `.env.example`; no worker/failure-retry operating model is committed.
- Mail uses the configured Laravel transport. The supported compose file does not define the `mailpit` service named by `.env.example`, so a clean compose-only mail path is incomplete.

## Observability

- Laravel logging channels are configured in `config/logging.php`.
- Telescope captures a restricted, redacted subset outside local and uses scheduled pruning; see `docs/operations/error-monitoring.md`.
- Activity history records specific account/Planning events with public identifiers and minimized metadata; see `docs/operations/activity-logging.md`.
- Planning cache inspection/invalidation is documented in `docs/operations/planning-cache.md`.
- No metrics, distributed tracing, external APM, or integration SLO configuration was found.

## Evidence

- `.env.example`, `docker-compose.yml`
- `config/database.php`, `config/cache.php`, `config/mail.php`, `config/services.php`
- `app/Modules/Planning/Services/PlanningService.php`
- `app/Modules/Planning/Services/PlanningCache.php`
- `app/Modules/ActivityLog/Services/ActivityRecorder.php`
- `app/Providers/TelescopeServiceProvider.php`
- `resources/js/bootstrap.ts`
- `php artisan route:list --except-vendor --json` (28 web routes during this pass)
