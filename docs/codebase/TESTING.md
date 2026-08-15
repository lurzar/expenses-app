# Testing patterns

This reference describes the configured test stack and the verified baseline on the v2.1.1 documentation branch. A failure is recorded as a failure; environment errors are not counted as passing behavior.

## Test stack and commands

- Backend framework: Pest 3.8.7 with Pest Laravel plugin 3.2.0 and PHPUnit underneath.
- Assertions: Pest expectations, PHPUnit assertions, Laravel HTTP/database/session assertions, and Inertia `AssertableInertia`.
- Mocking: Mockery 1.6.12 through Laravel's container-aware `$this->mock()`.
- Database isolation: `RefreshDatabase` applies to every `tests/Feature` test through `tests/Pest.php`.
- Static/build checks: Pint, strict TypeScript, Vite build, Composer/npm audits.
- Static analysis: no PHPStan/Larastan configuration exists yet.

```bash
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test
php artisan test --filter=PlanningAuthorization
vendor/bin/pint --test
npx tsc --noEmit
npm run build
composer audit --locked --no-interaction
npm audit --package-lock-only
```

Use PostgreSQL through Sail for database behavior that depends on PostgreSQL types, indexes, or constraints.

## Test layout

- `tests/Pest.php` binds Laravel's `TestCase` and `RefreshDatabase` to feature tests.
- `tests/Feature/Auth/` covers session authentication and account security flows.
- `tests/Feature/Planning/` covers cross-user authorization and user-scoped caching.
- `tests/Feature/System/` covers activity history, locale validation, Telescope, and trusted hosts.
- `tests/Feature/ProfileTest.php` covers profile updates/deletion.
- `tests/Unit/` currently contains only a scaffold example.
- Backend test files end in `Test.php` as configured in `phpunit.xml`.

## Scope matrix

| Scope | Covered? | Typical target | Notes |
| --- | --- | --- | --- |
| Unit | Minimal | Example scaffold | Domain calculations do not yet have dedicated unit coverage |
| Laravel feature/integration | Yes | Auth, profile, Planning cache/policy, activity, Telescope, language, trusted hosts | Real routes, middleware, Eloquent, SQLite, Inertia assertions |
| PostgreSQL-specific integration | No automated gate | Future money/index/migration behavior | Required for #63 where SQLite semantics are insufficient |
| Frontend unit/component | Not configured on this branch | React layouts/pages | #49 introduces the first focused runner/tests in a separate v2.1.1 PR |
| Browser/E2E | No | Full user journeys | No Playwright/Cypress configuration found |

## Isolation and mocking

- Safe host runs must explicitly override both `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`.
- Running `php artisan test` without the override uses the `.env`/`phpunit.xml` combination and attempted the Sail-only `pgsql` host during this pass: one unit test passed and 77 feature tests errored before behavior ran.
- Feature tests recreate database state through `RefreshDatabase`; factories create isolated users and Planning records.
- Security tests use separate owner/non-owner accounts rather than guessing public identifiers.
- Mocking is used at a narrow collaborator boundary to prove transaction rollback when `ActivityRecorder` fails.
- Telescope tests alter loaded configuration explicitly and validate fail-closed behavior.

## Verified baseline on 2026-08-16

With explicit in-memory SQLite:

- 68 tests passed, 10 failed, 224 assertions.
- Passing suites include Planning authorization/cache and System activity/language/Telescope/trusted-host coverage.
- Failures include removed/mismatched Auth views, Inertia responses affected by the Vite manifest entry mismatch, the example/profile page render path, and the Profile test's hard-delete expectation versus implemented soft deletion.
- PHPUnit emits a deprecated XML schema warning.

Other gates on the same branch:

- TypeScript: passed.
- Vite production build: passed, 797 modules transformed.
- Composer audit: no advisories (cache-directory warning did not change the audit result).
- npm audit: zero vulnerabilities.
- Repository-wide Pint: 31 files reported formatting findings.

These are baseline findings, not accepted passing gates. #65 owns deterministic database selection, test repair, Pint cleanup, static analysis, frontend gate integration, and CI parity.

## Coverage and quality signals

- `phpunit.xml` includes `app/` as coverage source but defines no enforced percentage and no coverage command in repository scripts.
- CI runs only for `main`, uses `npm install`, builds assets, and runs Pest against a SQLite file. It does not run the full documented matrix or active `v2.x`/version-branch PRs.
- Planning persistence validation/calculation paths lack authoritative money/tampering tests; #63 owns them after #57 records domain decisions.
- Owner-success paths and route-binding normalization remain for #61.
- Browser and frontend component coverage are absent until the relevant approved issues land.

## Evidence

- `composer.lock`, `phpunit.xml`, `tests/Pest.php`, `tests/TestCase.php`
- `tests/Feature/Planning/PlanningAuthorizationTest.php`
- `tests/Feature/Planning/PlanningCacheTest.php`
- `tests/Feature/System/ActivityLoggingTest.php`
- `.github/workflows/laravel.yml`
- Verified commands listed above, run on 2026-08-16

