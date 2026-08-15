# Codebase concerns

This register prioritizes verified current risks. It does not duplicate exploit details or claim planned fixes are implemented.

## Top risks

| Severity | Concern | Evidence | Impact | Approved action |
| --- | --- | --- | --- | --- |
| High | Persisted money trusts browser totals and uses binary float salary | `PlanningService::store`; `Planning::casts`; Planning migration | Tampered or rounded values can become authoritative | #57 decisions, then #63 implementation |
| High | Quality results depend on database/asset setup and CI does not cover active release branches | `phpunit.xml`; `.github/workflows/laravel.yml`; verified test runs | False confidence or release regressions | #65 |
| High | Expenses direct owner access fails before the intended ownership check | `{expenses}` does not bind to `$expense`; isolated owner-path request returns 403 | Owners cannot open the detail projection; intended record authorization is not exercised | #61 |
| Medium | Inertia payloads expose internal numeric `id`/`user_id` values alongside public ULIDs | `Planning`/`User` serialization; no API Resource or hidden fields | The public-identifier boundary is not enforced at the browser contract | #61 |
| Medium | Supported PostgreSQL environment conflicts with MySQL config fallback when `DB_CONNECTION` is absent | `.env.example`; `config/database.php` | An incomplete environment can target the wrong engine | #65/setup validation |
| Medium | Auth/Inertia page migration is incomplete | failing Auth feature tests; controllers returning removed Blade views | Verification/reset/confirmation pages return 500 | #65 or focused child issues |
| Medium | Planning list payloads are unbounded | `getAllPlannings()->get()`; Dashboard/Planning/Expenses props | Database, cache, serialization, and browser cost grow with history | Add approved pagination/query limits |
| Medium | Development mail configuration names a service absent from Compose | `.env.example`; `docker-compose.yml` | Verification/reset mail cannot work in a compose-only setup | Add/replace documented local mail service |

## Technical debt

| Debt item | Current evidence | Risk if ignored | Suggested fix |
| --- | --- | --- | --- |
| Client-calculated totals | `Planning/Create.tsx`; `PlanningStoreRequest`; `PlanningService` | Integrity and precision drift | Complete #57/#63 with typed server calculations and migration |
| Broad string/array validation | `PlanningStoreRequest.php` | Negative, malformed, excessive, duplicate-period data | Domain-specific request rules plus DB constraints in #63 |
| Singleton service holds a mutable model instance | `PlanningServiceProvider.php`; `PlanningService.php` | Reuse can mutate an already-persisted instance in multi-create flows | Inject a model factory/repository or call `newQuery()->create()` when scope is approved |
| Removed-view test/controller mismatch | Auth controllers and feature tests | User-facing 500 responses and red baseline | Repair Inertia pages/controllers/tests under #65 children |
| Repository formatting drift | verified Pint output (31 files) | Review noise and inconsistent enforcement | Ratchet/clean under #65 |
| No Laravel-aware static analysis | `composer.json` | Type/null/query defects remain compile-time invisible | Add Larastan/PHPStan with explicit baseline policy in #65 |
| Scaffold tests/helpers remain | `tests/Feature/ExampleTest.php`; `tests/Unit/ExampleTest.php`; `tests/Pest.php::something` | Noise and misleading coverage | Remove or replace while repairing #65 baseline |
| Workflow history uses squash aggregation | recent Git history | Per-file churn counts collapse into large release commits | Use issues/PRs plus file size/ownership as change-risk evidence |

## Security and data-integrity concerns

| Risk | Category | Current mitigation | Gap |
| --- | --- | --- | --- |
| Cross-user Planning access | OWASP A01 Broken Access Control | `PlanningPolicy`, Gate calls, user-scoped collections, non-owner tests | Normalize binding and add owner-success/CI enforcement in #61 |
| Financial tampering/precision | OWASP A04 Insecure Design / data integrity | Request shape validation only | Server authority, precision, rounding, uniqueness, migration in #63 |
| Diagnostic data exposure | OWASP A09 Logging/Monitoring | Explicit non-local enablement, verified allowlist, redaction, pruning tests/docs | No external monitoring/alerting/SLO is configured |
| Activity metadata privacy | N/A | Enum event contract, public ULIDs, allowlisted fields, atomic writes | Access/reporting interface and production retention ownership are not defined |
| Trusted host/locale abuse | OWASP A05 Security Misconfiguration | Global trusted-host middleware and locale allowlist tests | Keep coverage in mandatory CI under #65 |
| Public API/token surface | OWASP A01/A07 if introduced | No public API exists; session/CSRF web model | #58 must approve consumers/auth/contract before endpoints are added |

## Performance and scaling concerns

| Concern | Evidence | Current symptom | Scaling risk | Suggested improvement |
| --- | --- | --- | --- | --- |
| Unpaginated Planning collections | `PlanningService::getAllPlannings` | Every view receives all user plans | Cache/payload/render growth | Approve pagination or period-limited queries |
| Full collection cached for five minutes | `PlanningCache` | Repeated reads are fast but values grow together | Large serialization and invalidation payloads | Cache bounded/paginated representations |
| Large calculation form component | `Planning/Create.tsx` (416 source lines) | UI state, item editing, and calculations are coupled | Fragile UI changes and hard unit testing | Split only after v2.1.3 UI/UX decisions |
| Translation dictionaries loaded through per-request file glob | `HandleInertiaRequests::getTranslations` | Every Inertia response assembles dictionaries | Extra filesystem/prop work as dictionaries grow | Cache/limit shared translations after measuring |
| Synchronous queue | `.env.example` | Mail/tasks run in request lifecycle | Latency and failure coupling if workload expands | Approve worker/retry operations before async adoption |

## Fragile or high-change areas

| Area | Why fragile | Signal | Safe change strategy |
| --- | --- | --- | --- |
| Planning create flow | Money meaning, dynamic arrays, and 416-line React component | Largest application source file | Decide domain/UI contracts first; add calculation and component tests |
| Auth UI/controllers | Blade-to-Inertia migration is incomplete | Multiple verified feature failures | Fix one route/page contract per test-first issue |
| Telescope provider/tests | Security-sensitive filtering/redaction/retention | Provider ~199 lines, test file ~269 lines | Run focused security suite and diff review |
| Activity history | Atomicity and privacy allowlist | Test file ~367 lines | Preserve event contract and transaction tests |
| Release workflow/docs | Branch/release policy recently changed | Highest useful recent churn after excluding squashed metadata | Verify live GitHub state before every release edit |

Recent 90-day churn is distorted by squash release commits: most migrated files appear once. File size, failing tests, issue ownership, and security/data criticality are more useful risk signals than raw counts in this repository.

## `[ASK USER]` questions

1. [ASK USER] Approve or revise the RM representation, rounding, saving-rate, and user/month uniqueness proposal recorded by #57 before #63 changes persistence.
2. [ASK USER] Is a public API actually intended; if so, who is the first consumer and what authentication/compatibility contract should its implementation issue approve under #58 governance?
3. [ASK USER] What production deployment platform, secret store, database backup/restore owner, and recovery objective are supported?
4. [ASK USER] Which production mail provider and asynchronous queue/worker model, if any, should replace the current local/synchronous placeholders?
5. [ASK USER] What maximum Planning history/payload should list, Dashboard, and Expenses views support before pagination is required?

These questions are recorded for their owning issues and do not block publishing current-code reference documentation.

## Evidence

- `app/Modules/Planning/Services/PlanningService.php`
- `app/Modules/Planning/Models/Planning.php`
- `app/Modules/Planning/Requests/PlanningStoreRequest.php`
- `app/Modules/Expenses/routes.php`, `ExpensesController.php`
- `resources/js/Pages/Planning/Create.tsx`
- `phpunit.xml`, `.github/workflows/laravel.yml`
- `.env.example`, `docker-compose.yml`, `config/database.php`
- Verified route, test, Pint, TypeScript, build, and audit commands from 2026-08-16
