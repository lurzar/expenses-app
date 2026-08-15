# Codebase concerns

This register prioritizes verified current risks. It does not duplicate exploit details or claim planned fixes are implemented.

## Top risks

| Severity | Concern | Evidence | Impact | Approved action |
| --- | --- | --- | --- | --- |
| High | Quality enforcement relies on exact-head local execution because hosted CI is intentionally disabled | `composer check`; `tests/bootstrap.php`; verified test runs | A release owner could omit or misreport a gate | Record locked-install and full-gate evidence on every PR and release tracker under #65 policy |
| Medium | Supported PostgreSQL environment conflicts with MySQL config fallback when `DB_CONNECTION` is absent | `.env.example`; `config/database.php` | An incomplete environment can target the wrong engine | #65/setup validation |
| Medium | Planning list payloads are unbounded | `getAllPlannings()->get()`; Dashboard/Planning/Expenses props | Database, cache, serialization, and browser cost grow with history | Add approved pagination/query limits |
| Medium | Development mail configuration names a service absent from Compose | `.env.example`; `docker-compose.yml` | Verification/reset mail cannot work in a compose-only setup | Add/replace documented local mail service |

## Technical debt

| Debt item | Current evidence | Risk if ignored | Suggested fix |
| --- | --- | --- | --- |
| Auth/Inertia route/page contracts | repaired Auth feature tests and Inertia pages | User-facing 500 responses if contracts drift | Preserve feature coverage in the local exact-head gate |
| Repository formatting | repository-wide Pint cleanup and `composer format:test` | Review noise if the gate is skipped | Keep the local exact-head gate green |
| Laravel-aware static analysis | Larastan/PHPStan level 8 without a baseline | Type/null/query regressions if skipped | Keep `composer analyse` in the local exact-head gate |
| Scaffold tests/helpers remain | `tests/Feature/ExampleTest.php`; `tests/Unit/ExampleTest.php`; `tests/Pest.php::something` | Noise and misleading coverage | Remove or replace through a focused cleanup issue |
| Workflow history uses squash aggregation | recent Git history | Per-file churn counts collapse into large release commits | Use issues/PRs plus file size/ownership as change-risk evidence |

## Security and data-integrity concerns

| Risk | Category | Current mitigation | Gap |
| --- | --- | --- | --- |
| Cross-user Planning access | OWASP A01 Broken Access Control | Public-ULID binding, `PlanningPolicy`, user-scoped collections, explicit data DTOs, owner/non-owner tests | Preserve the complete owner-success and denial matrix when adding record routes |
| Financial tampering/precision | OWASP A04 Insecure Design / data integrity | Exact decimal validation, integer-sen calculator, server totals, fixed-precision persistence, partial unique index, migration and tampering tests | Preserve PostgreSQL migration review and exact payload coverage |
| Diagnostic data exposure | OWASP A09 Logging/Monitoring | Explicit non-local enablement, verified allowlist, redaction, pruning tests/docs | No external monitoring/alerting/SLO is configured |
| Activity metadata privacy | N/A | Enum event contract, public ULIDs, allowlisted fields, atomic writes | Access/reporting interface and production retention ownership are not defined |
| Trusted host/locale abuse | OWASP A05 Security Misconfiguration | Global trusted-host middleware and locale allowlist tests | Keep coverage in the mandatory local exact-head gate under #65 |
| Public API/token surface | OWASP A01/A07 if introduced | No public API exists; session/CSRF web model | #58 must approve consumers/auth/contract before endpoints are added |

## Performance and scaling concerns

| Concern | Evidence | Current symptom | Scaling risk | Suggested improvement |
| --- | --- | --- | --- | --- |
| Unpaginated Planning collections | `PlanningService::getAllPlannings` | Every view receives all user plans | Cache/payload/render growth | Approve pagination or period-limited queries |
| Full collection cached for five minutes | `PlanningCache` | Repeated reads are fast but values grow together | Large serialization and invalidation payloads | Cache bounded/paginated representations |
| Large calculation form component | `Planning/Create.tsx` | UI state and item editing remain coupled despite the extracted exact-money helper | Fragile UI changes | Split through focused UI implementation issues #123/#124 |
| Translation dictionaries loaded through per-request file glob | `HandleInertiaRequests::getTranslations` | Every Inertia response assembles dictionaries | Extra filesystem/prop work as dictionaries grow | Cache/limit shared translations after measuring |
| Synchronous queue | `.env.example` | Mail/tasks run in request lifecycle | Latency and failure coupling if workload expands | Approve worker/retry operations before async adoption |

## Fragile or high-change areas

| Area | Why fragile | Signal | Safe change strategy |
| --- | --- | --- | --- |
| Planning create flow | Dynamic arrays and a large React component remain coupled | Largest application source file | Preserve #63 money tests and split presentation through #123/#124 |
| Auth UI/controllers | Security-sensitive session, reset, verification, and confirmation flows span Laravel and Inertia | Multiple route/page contracts | Keep each route/page contract covered by focused feature, type, and build checks |
| Telescope provider/tests | Security-sensitive filtering/redaction/retention | Provider ~199 lines, test file ~269 lines | Run focused security suite and diff review |
| Activity history | Atomicity and privacy allowlist | Test file ~367 lines | Preserve event contract and transaction tests |
| Release workflow/docs | Branch/release policy recently changed | Highest useful recent churn after excluding squashed metadata | Verify live GitHub state before every release edit |

Recent 90-day churn is distorted by squash release commits: most migrated files appear once. File size, failing tests, issue ownership, and security/data criticality are more useful risk signals than raw counts in this repository.

## `[ASK USER]` questions

1. [ASK USER] Is a public API actually intended; if so, who is the first consumer and what authentication/compatibility contract should its implementation issue approve under #58 governance?
2. [ASK USER] What production deployment platform, secret store, database backup/restore owner, and recovery objective are supported?
3. [ASK USER] Which production mail provider and asynchronous queue/worker model, if any, should replace the current local/synchronous placeholders?
4. [ASK USER] What maximum Planning history/payload should list, Dashboard, and Expenses views support before pagination is required?

These questions are recorded for their owning issues and do not block publishing current-code reference documentation.

## Evidence

- `app/Modules/Planning/Services/PlanningService.php`
- `app/Modules/Planning/Models/Planning.php`
- `app/Modules/Planning/Requests/PlanningStoreRequest.php`
- `app/Modules/Expenses/routes.php`, `ExpensesController.php`
- `resources/js/Pages/Planning/Create.tsx`
- `composer.json`, `phpunit.xml`, `tests/bootstrap.php`, `tests/TestCase.php`
- `.env.example`, `docker-compose.yml`, `config/database.php`
- Verified route, test, Pint, TypeScript, build, and audit commands from 2026-08-16
