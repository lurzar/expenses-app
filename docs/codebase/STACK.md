# Technology stack

This reference is for maintainers and contributors who need the supported runtimes, locked framework versions, and tool boundaries before changing the application.

## Runtime summary

| Area | Value | Evidence |
| --- | --- | --- |
| Backend language | PHP `^8.4`; Composer resolves as PHP 8.4.0 | `composer.json` (`require.php`, `config.platform.php`) |
| Backend framework | Laravel 12.66.0 | `composer.lock`; `composer show --locked --direct` |
| Frontend language | TypeScript 5.9 with strict checking and React TSX | `package-lock.json`; `tsconfig.json` |
| Frontend runtime | React 19.2.8 through Inertia 2.3.27 | `package-lock.json`; `resources/js/app.tsx` |
| Supported Node.js | Node.js 22 | `README.md` |
| PHP package manager | Composer 2 with a committed lock | `composer.json`; `composer.lock` |
| Frontend package manager | npm with lockfile version 3 | `package.json`; `package-lock.json` |
| Build system | Vite 6.4.3 with Laravel and React plugins | `package-lock.json`; `vite.config.js` |

The host versions observed during this documentation pass were PHP 8.5.9 and Node.js 26.7.0. They are discovery evidence, not a change to the supported PHP 8.4/Node.js 22 matrix.

## Production frameworks and dependencies

| Dependency | Locked version | Role | Evidence |
| --- | --- | --- | --- |
| `laravel/framework` | 12.66.0 | HTTP lifecycle, service container, validation, authorization, ORM, cache, queue, mail | `composer.lock` |
| `inertiajs/inertia-laravel` | 2.0.25 | Laravel-to-Inertia response adapter | `composer.lock`; `app/Http/Middleware/HandleInertiaRequests.php` |
| `@inertiajs/react` | 2.3.27 | React page resolution, navigation, forms, shared props | `package-lock.json`; `resources/js/app.tsx` |
| `react` / `react-dom` | 19.2.8 | Browser component runtime and rendering | `package-lock.json`; `resources/js/app.tsx` |
| `laravel/sanctum` | 4.3.3 | Token capability on `User`; no public API routes are implemented | `composer.lock`; `app/Models/User.php`; route inventory |
| `tightenco/ziggy` / `ziggy-js` | 2.6.3 | Named Laravel routes in TypeScript | `composer.lock`; `package-lock.json`; `resources/js/utils/route.ts` |
| `guzzlehttp/guzzle` | 7.15.3 | Available HTTP client; no application-owned outbound call was found | `composer.lock`; source search |
| Tailwind CSS | 3.4.19 | Utility CSS and class-based dark mode | `package-lock.json`; `tailwind.config.js` |

## Development toolchain

| Tool | Purpose | Evidence |
| --- | --- | --- |
| Laravel Sail | PHP 8.4/Docker development runtime | `composer.lock`; `docker-compose.yml` |
| Pest 3.8.7 + Laravel plugin 3.2.0 | Backend unit and feature tests | `composer.lock`; `tests/Pest.php` |
| Mockery 1.6.12 | Focused dependency mocks | `composer.lock`; `tests/Feature/System/ActivityLoggingTest.php` |
| Vitest 4.1.1 + Testing Library 16.3.2 + jsdom 27.4.0 | Frontend theme hook, DOM, component, and integration-source regression tests | `package-lock.json`; `resources/js/theme.test.tsx` |
| Laravel Pint 1.30.5 | PHP formatting | `composer.lock`; `AGENTS.md` |
| TypeScript | Strict static type checking without emission | `tsconfig.json`; `AGENTS.md` |
| Vite | Development server and production assets | `vite.config.js`; `package.json` |
| Telescope | Conditional diagnostics and pruning | `composer.lock`; `app/Providers/TelescopeServiceProvider.php` |
| Laravel Debugbar | Local request diagnostics | `composer.lock`; `config/debugbar.php` when vendor config is published |
| Local quality gate | `composer check` runs formatting, level-8 Larastan, backend/frontend tests, TypeScript, build, and locked audits | `composer.json`; `docs/development/workflow.md` |

Larastan/PHPStan is committed at level 8 without a baseline or ignored errors. No ESLint or Prettier configuration is committed. Hosted GitHub Actions are intentionally not used; issue #65 owns the approved deterministic local quality policy.

## Key commands

```bash
composer install
npm ci
composer validate --strict
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test
npm test
vendor/bin/pint --test
npx tsc --noEmit
npm run build
composer audit --locked --no-interaction
npm audit --package-lock-only
```

Use Sail instead of SQLite when the behavior depends on PostgreSQL semantics. Never run tests with an ambiguous database connection; the checked-in development host `pgsql` is resolvable inside Sail, not from a normal host process.

## Environment and configuration

- Application configuration is loaded from `.env` into `config/*.php`; `.env.example` is the committed variable contract.
- Supported local services are PHP 8.4, PostgreSQL 18.4, Redis 8.8.1, and pgAdmin 9.16 in `docker-compose.yml`.
- The supported application database is PostgreSQL (`DB_CONNECTION=pgsql` in `.env.example`). SQLite is used for compatible isolated tests.
- The default application cache is the file store; Redis is optional (`config/cache.php`, `.env.example`).
- Queues are synchronous and mail points to an SMTP `mailpit` host in `.env.example`; the compose file does not currently define a Mailpit service.
- Diagnostics and audit history are controlled by `TELESCOPE_*` and `ACTIVITY_LOG_*` variables. Operational rules live under `docs/operations/`.

Do not commit `.env`, credentials, logs, caches, Debugbar payloads, Telescope entries, or production identifiers.

## Evidence

- `composer.json`, `composer.lock`
- `package.json`, `package-lock.json`
- `tsconfig.json`, `vite.config.js`, `tailwind.config.js`
- `.env.example`, `docker-compose.yml`
- `tests/bootstrap.php`, `phpstan.neon`, `composer.json`
- `bootstrap/app.php`, `resources/js/app.tsx`
