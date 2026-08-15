<div align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="./public/images/logo-white.png">
    <source media="(prefers-color-scheme: light)" srcset="./public/images/logo-black.png">
    <img src="./public/images/logo-black.png" alt="Expenses App" width="420">
  </picture>

  <p>Plan a month, understand the allocation, and replace a manual expense-planning spreadsheet with one web application.</p>
</div>

## Overview

Expenses App is a personal monthly-planning application. A user records income and planned savings, commitments, and other spending for a month. The application stores that plan and presents it through Planning, Dashboard, and Expenses views.

The current Expenses view is a projection of monthly Planning data. It is not a transaction ledger, and the application does not expose a public REST API.

## Current capabilities

- Register, sign in, sign out, and manage a profile.
- Create, review, list, and delete monthly plans.
- Organize planned amounts into savings, commitments, and other spending.
- Review planning summaries through Dashboard and Expenses views.
- Switch between English and Malay interface dictionaries.
- Run locally with Laravel Sail, PostgreSQL, Redis, and pgAdmin.

## Architecture

Expenses App is a Laravel 12 modular monolith. Laravel modules own the web routes and server-side orchestration; Inertia 2 connects them to a React 19 and TypeScript frontend.

Planning is the central stored aggregate. Dashboard and Expenses read Planning data through the Planning service. PostgreSQL stores application data, while Redis is available to the local stack for future cache and queue use.

See the [documentation map](docs/README.md) for current references and issue-backed planned documentation.

## Getting started

### Prerequisites

- PHP 8.4
- Composer 2
- Docker with Docker Compose
- Node.js 22 and npm

> [!IMPORTANT]
> The development stack uses PostgreSQL 18. If you already have an Expenses App database volume created by PostgreSQL 17 or earlier, follow the [PostgreSQL 18 upgrade guide](docs/postgresql-18-upgrade.md) before starting the updated stack.

### Set up the application

```bash
git clone https://github.com/lurzar/expenses-app.git
cd expenses-app
composer install
cp .env.example .env
php artisan key:generate
npm ci
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
npm run dev
```

Open [http://localhost:8080](http://localhost:8080). The port comes from `APP_PORT` in `.env.example`.

### Run the available checks

```bash
composer validate --strict
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test
vendor/bin/pint --test
npx tsc --noEmit
npm run build
composer audit --locked
npm audit --package-lock-only
```

The repository is still establishing a deterministic quality baseline in [issue #65](https://github.com/lurzar/expenses-app/issues/65). Treat a local environment failure as a failure to investigate, not as a passing check.

## Documentation

- [Documentation map and ownership](docs/README.md)
- [PostgreSQL 18 local-volume upgrade](docs/postgresql-18-upgrade.md)
- [Release history](CHANGELOG.md)
- [Version 2.0.8 plan](https://github.com/lurzar/expenses-app/issues/82)

GitHub Issues hold planned work. Repository documentation describes approved, current behavior. When the two disagree, verify the source and update the stale issue or document.
