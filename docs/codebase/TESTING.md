# Testing patterns

This reference describes the deterministic test and quality stack established for v2.1.2. A failure is recorded as a failure; environment errors are not counted as passing behavior.

## Test stack and commands

- Backend framework: Pest 3.8.7 with Pest Laravel plugin 3.2.0 and PHPUnit underneath.
- Assertions: Pest expectations, PHPUnit assertions, Laravel HTTP/database/session assertions, and Inertia `AssertableInertia`.
- Mocking: Mockery 1.6.12 through Laravel's container-aware `$this->mock()`.
- Frontend: Vitest 4.1.1, React Testing Library 16.3.2, and jsdom 27.4.0.
- Database isolation: `RefreshDatabase` applies to every `tests/Feature` test through `tests/Pest.php`.
- Static/build checks: Pint, Larastan/PHPStan, strict TypeScript, Vite build, and Composer/npm audits.
- Aggregate local gate: `composer check` runs every configured check after locked dependencies are installed.

```bash
composer validate --strict
composer format:test
composer analyse
composer test
npm run test
npm run typecheck
npm run build
composer audit --locked --no-interaction
npm audit --package-lock-only
composer check
```

Use PostgreSQL through Sail for database behavior that depends on PostgreSQL types, indexes, or constraints.

## Test layout

- `tests/Pest.php` binds Laravel's `TestCase` and `RefreshDatabase` to feature tests.
- `tests/Feature/Auth/` covers session authentication and account security flows.
- `tests/Feature/Planning/` covers cross-user authorization and user-scoped caching.
- `tests/Feature/System/` covers activity history, locale validation, Telescope, and trusted hosts.
- `tests/Feature/ProfileTest.php` covers profile updates/deletion.
- `tests/Unit/` currently contains only a scaffold example.
- Backend test files end in `Test.php` as configured in `phpunit.xml`; rendered Auth/Profile routes are asserted as Inertia responses.

## Scope matrix

| Scope | Covered? | Typical target | Notes |
| --- | --- | --- | --- |
| Unit | Minimal | Example scaffold | Domain calculations do not yet have dedicated unit coverage |
| Laravel feature/integration | Yes | Auth, profile, Planning cache/policy, activity, Telescope, language, trusted hosts | Real routes, middleware, Eloquent, SQLite, Inertia assertions |
| PostgreSQL-specific integration | No automated gate | Future money/index/migration behavior | Required for #63 where SQLite semantics are insufficient |
| Frontend unit/component | Focused | Theme resolver/hook, root DOM state, storage failures, shared toggle, layout wiring, pre-Vite bootstrap | `resources/js/theme.test.tsx`; feature issues add focused tests |
| Browser/E2E | No | Full user journeys | No Playwright/Cypress configuration found |

## Isolation and mocking

- `tests/bootstrap.php` clears `DATABASE_URL` and overwrites process, server, and environment values for both `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:` before Laravel loads; `phpunit.xml` repeats the contract. Default `composer test` and `php artisan test` runs therefore cannot inherit or reconstruct the Sail/development PostgreSQL connection.
- The base feature test case disables Vite manifest resolution. Backend tests are therefore reproducible before a production asset build exists; the aggregate gate still builds the assets explicitly.
- Do not bypass the forced test configuration. Use a separate explicit configuration when validating PostgreSQL-only behavior.
- Feature tests recreate database state through `RefreshDatabase`; factories create isolated users and Planning records.
- Security tests use separate owner/non-owner accounts rather than guessing public identifiers.
- Mocking is used at a narrow collaborator boundary to prove transaction rollback when `ActivityRecorder` fails.
- Telescope tests alter loaded configuration explicitly and validate fail-closed behavior.

## Verified v2.1.2 gate on 2026-08-16

- Backend: 79 tests passed with 319 assertions; no failures or deprecated PHPUnit schema warning.
- Repository-wide Pint: clean.
- Larastan/PHPStan: level 8, 77 application/database files, no errors, no baseline or ignored error.
- Frontend tests: 13 passed.
- Strict TypeScript: passed.
- Vite production build: passed, 803 modules transformed after the four Auth pages were added.
- Composer audit: no advisories.
- npm audit: zero vulnerabilities.

## Coverage and quality signals

- `phpunit.xml` includes `app/` as coverage source but defines no enforced percentage; coverage is intentionally not presented as a gate until a measured ratchet is approved.
- GitHub-hosted Actions are not used under the approved zero-cost personal-account policy. The release owner runs `npm ci` and `composer check` locally on every exact PR/release head and records the results in the PR and release tracker.
- Planning persistence validation/calculation paths lack authoritative money/tampering tests; #63 owns them after #57 records domain decisions.
- Owner-success paths and route-binding normalization remain for #61.
- Browser/E2E coverage remains absent; focused component and Laravel feature coverage protects current behavior.

## Evidence

- `composer.lock`, `phpunit.xml`, `tests/Pest.php`, `tests/TestCase.php`
- `tests/Feature/Planning/PlanningAuthorizationTest.php`
- `tests/Feature/Planning/PlanningCacheTest.php`
- `tests/Feature/System/ActivityLoggingTest.php`
- `resources/js/theme.test.tsx`
- Verified commands listed above, run on 2026-08-16
