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
- Enforce module-owned roles and permissions through Laravel Gate and owner-aware policies.
- Provision and rotate protected super-admin access through an audited server-side workflow without default production credentials.
- Enter a responsive, bilingual Admin control plane when the authenticated account has the server-issued `admin.access` capability.
- Switch between the shipped English and Malay interface dictionaries from guest and authenticated layouts.
- Run locally with Laravel Sail, PostgreSQL, Redis, and pgAdmin.

## Architecture

Expenses App is a Laravel 12 modular monolith. Laravel modules own the web routes and server-side orchestration; Inertia 2 connects them to a React 19 and TypeScript frontend.

Planning is the central stored aggregate. Dashboard and Expenses read Planning data through the Planning service. PostgreSQL stores application data. Laravel's configured cache store accelerates each user's Planning collection reads; the file store remains the default, and Redis is optional.

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

### Run the quality gates

```bash
composer check
```

The committed test bootstrap clears `DATABASE_URL` and forces in-memory SQLite before Laravel loads, preventing exported shell values from selecting the Sail/development database. Backend tests do not require a pre-existing Vite manifest. `composer check` runs formatting, Larastan, backend and frontend tests, TypeScript, production build, and locked dependency audits. Treat any local environment failure as a failure to investigate, not as a passing check.

## Documentation

- [Documentation map and ownership](docs/README.md)
- [Current codebase architecture](docs/codebase/ARCHITECTURE.md)
- [Expense-planning domain and data flow](docs/domain/expense-planning.md)
- [Web/Inertia interfaces and future API governance](docs/interfaces/web-inertia.md)
- [Development and release workflow](docs/development/workflow.md)
- [PostgreSQL 18 local-volume upgrade](docs/postgresql-18-upgrade.md)
- [Release history](CHANGELOG.md)

GitHub Issues hold planned work. Repository documentation describes approved, current behavior. When the two disagree, verify the source and update the stale issue or document.
