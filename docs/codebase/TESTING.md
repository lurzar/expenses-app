# Testing patterns

This reference describes the deterministic test and quality stack established for v2.1.2. A failure is recorded as a failure; environment errors are not counted as passing behavior.

## Test stack and commands

- Backend framework: Pest 3.8.7 with Pest Laravel plugin 3.2.0 and PHPUnit underneath.
- Assertions: Pest expectations, PHPUnit assertions, Laravel HTTP/database/session assertions, and Inertia `AssertableInertia`.
- Mocking: Mockery 1.6.13 through Laravel's container-aware `$this->mock()`.
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
- `tests/Feature/Authorization/` covers schema lifecycle, protected roles, Gate/policy matrices, drift-preserving synchronization, capability props, transactional activity, super-admin provisioning/rotation, last-operator protection, and authorization-session revocation.
- `tests/Feature/Planning/` covers cross-user authorization and user-scoped caching.
- `tests/Feature/System/` covers activity history, locale validation, Telescope, and trusted hosts.
- `tests/Feature/ProfileTest.php` covers profile updates/deletion.
- `tests/Unit/` currently contains only a scaffold example.
- Backend test files end in `Test.php` as configured in `phpunit.xml`; rendered Auth/Profile routes are asserted as Inertia responses.

## Scope matrix

| Scope | Covered? | Typical target | Notes |
| --- | --- | --- | --- |
| Unit | Focused | Exact MYR parsing/formatting, half-up saving target, canonical Planning calculation | Preserve integer-sen boundary coverage |
| Laravel feature/integration | Yes | Auth, profile, Planning cache/policy, activity, Telescope, language, trusted hosts | Real routes, middleware, Eloquent, SQLite, Inertia assertions |
| PostgreSQL-specific integration | Manual release evidence | Money/index/migration SQL and representative data | SQLite tests cover up/down normalization and partial-index behavior; PostgreSQL deployment still requires explicit SQL review |
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

## Verified v2.1.4 #63 gate on 2026-08-16

- Backend: 122 tests passed with 564 assertions, including cache-namespace, migration up/down/failure, supported seeding, tampering, precision, rounding, duplicate period, and projection payload coverage.
- Repository-wide Pint: clean.
- Larastan/PHPStan: level 8, 83 application/database files, no errors, no baseline or ignored error.
- Frontend tests: 22 passed across exact money preview, theme, language-route, and Planning/Expenses public-route contracts.
- Strict TypeScript: passed.
- Vite production build: passed, 804 modules transformed.
- Composer audit: no advisories.
- npm audit: zero vulnerabilities.

## Coverage and quality signals

- `phpunit.xml` includes `app/` as coverage source but defines no enforced percentage; coverage is intentionally not presented as a gate until a measured ratchet is approved.
- GitHub-hosted Actions are not used under the approved zero-cost personal-account policy. The release owner runs `npm ci` and `composer check` locally on every exact PR/release head and records the results in the PR and release tracker.
- Planning persistence has authoritative money, tampering, precision, uniqueness, migration, rollback, and payload tests under #63.
- Planning/Expenses owner success, non-owner denial, public-ULID binding, deletion, and DTO serialization are covered together in `PlanningAuthorizationTest`.
- Browser/E2E coverage remains absent; focused component and Laravel feature coverage protects current behavior.

## Evidence

- `composer.lock`, `phpunit.xml`, `tests/Pest.php`, `tests/TestCase.php`
- `tests/Feature/Planning/PlanningAuthorizationTest.php`
- `tests/Feature/Planning/PlanningCacheTest.php`, `PlanningMoneyIntegrityTest.php`, and `PlanningMoneyMigrationTest.php`
- `tests/Feature/System/ActivityLoggingTest.php`
- `resources/js/theme.test.tsx`, `resources/js/money.test.ts`
- `resources/js/language-route.test.ts`, `resources/js/planning-route.test.ts`
- Verified commands listed above, run on 2026-08-16
