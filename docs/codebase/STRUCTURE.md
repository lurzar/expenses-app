# Codebase structure

This reference maps source-owned directories and entry points. Generated output, installed dependencies, caches, logs, and local diagnostic payloads are not source conventions.

## Top-level map

| Path | Purpose | Evidence |
| --- | --- | --- |
| `app/` | Shared Laravel core plus feature modules | `bootstrap/providers.php`; namespaces under `app/` |
| `app/Modules/` | Feature-oriented modules, each owning relevant routes/controllers/services/data | module service providers and `routes.php` files |
| `bootstrap/` | Laravel application construction and provider registration | `bootstrap/app.php`; `bootstrap/providers.php` |
| `config/` | Environment-backed framework and application configuration | `config/*.php` |
| `database/` | Shared user/framework migrations, factories, and seeders | `database/migrations/`; `database/factories/` |
| `resources/js/` | React/Inertia layouts, pages, shared types, utilities, and frontend entry | `resources/js/app.tsx` |
| `resources/views/` | Root Blade shell used by Inertia | `resources/views/app.blade.php` |
| `resources/css/` | Tailwind CSS entry and project CSS | `resources/css/app.css` |
| `tests/` | Pest unit and Laravel feature suites | `tests/Pest.php`; `phpunit.xml` |
| `docs/` | Canonical current project, development, operations, and architecture knowledge | `docs/README.md` |
| `.github/` | GitHub metadata and thin tool-specific guidance | `.github/copilot-instructions.md` |
| `public/` | HTTP entry point and static assets; built assets are generated | `public/index.php`; `.gitignore` |
| `storage/` | Runtime logs, caches, sessions, and local diagnostics; not source | `.gitignore`; directory `.gitignore` files |

`vendor/`, `node_modules/`, `public/build/`, `bootstrap/cache/`, and runtime `storage/` content must not be used to infer source organization.

## Entry points

- HTTP: `public/index.php` loads Composer and `bootstrap/app.php`.
- Application construction: `bootstrap/app.php` adds trusted-host, language, authorization-session, and Inertia middleware and the health/console routes.
- Provider/module registration: `bootstrap/providers.php` loads shared and feature service providers.
- Module web routes: each module provider calls `loadRoutesFrom()` on its own `app/Modules/<Module>/routes.php`.
- Browser application: `resources/js/app.tsx` resolves `resources/js/Pages/**/*.tsx` through Inertia and mounts React.
- Root HTML: `resources/views/app.blade.php` supplies the Inertia root and Vite entries.
- CLI/scheduling: `artisan`, `routes/console.php`, and module console registration such as `ActivityLogServiceProvider`.
- Background workers: no application-owned worker process is configured; `.env.example` uses `QUEUE_CONNECTION=sync`.

## Module boundaries

| Boundary | Owns | Must not own | Evidence |
| --- | --- | --- | --- |
| `ActivityLog` | Allowlisted activity events, persistence, pruning | General application logging or arbitrary metadata | `ActivityEvent.php`; `ActivityRecorder.php` |
| `Auth` | Session authentication, registration, password and email-verification routes | Planning rules or profile persistence | `AuthServiceProvider.php`; `Auth/routes.php` |
| `Authorization` | Permission catalog, protected roles, Gate registration, role assignment, super-admin lifecycle, session-version enforcement, and authorization commands | Module-owned record policies or browser-only enforcement | `AuthorizationServiceProvider.php`; `SuperAdminLifecycleService.php` |
| `Dashboard` | Authenticated summary page over Planning data | A second Planning data model | `DashboardController.php` |
| `Expenses` | Read-only projection of Planning collections/records | A transaction ledger or expense-entry persistence | `ExpensesController.php`; README |
| `Landing` | Public landing page | Authenticated domain behavior | `LandingController.php` |
| `Language` | Locale allowlist, session selection, dictionaries | Domain calculations | `LanguageManager.php`; `Language/routes.php` |
| `Planning` | Monthly-plan aggregate, policy, storage, cache, and orchestration | Profile/auth ownership or speculative ledger data | `PlanningServiceProvider.php`; `PlanningService.php` |
| `Profile` | Profile update and soft deletion lifecycle, including calls to shared protected-role invariants | Role administration | `ProfileController.php`; `SuperAdminLifecycleService.php` |
| Shared `app/Models`, `Traits`, `Providers` | Cross-module user, public-ID, startup, and diagnostics behavior | Feature-specific page/controller logic | `User.php`; `HasPublicId.php`; providers |

## Naming and organization rules

- PHP classes and files use PascalCase under PSR-4 namespaces, for example `PlanningService.php` and `App\Modules\Planning\Services\PlanningService`.
- Module directories are PascalCase nouns; subdirectories use Laravel responsibility names such as `Controllers`, `Models`, `Requests`, and `Services`.
- Module route files are lowercase `routes.php`; database artifacts live under the owning module when module-specific.
- React pages/layouts use PascalCase TSX filenames. Shared frontend utilities use lowercase/camel-style filenames such as `utils/route.ts`.
- The `@/*` alias maps to `resources/js/*` in both `tsconfig.json` and `vite.config.js`.
- Laravel routes use dot-separated names (`planning.show`, `expenses.index`). Public model binding uses ULID columns through `HasPublicId`.

## Evidence

- `public/index.php`, `bootstrap/app.php`, `bootstrap/providers.php`
- `app/Modules/*/*ServiceProvider.php`
- `app/Modules/*/routes.php`
- `resources/js/app.tsx`, `resources/views/app.blade.php`
- `composer.json`, `tsconfig.json`, `vite.config.js`
- `docs/README.md`
