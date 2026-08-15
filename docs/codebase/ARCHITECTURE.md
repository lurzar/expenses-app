# Architecture

This explanation describes the implemented architecture. It does not turn planned issues into current behavior.

## Architectural style

Expenses App is a Laravel modular monolith with feature-oriented modules and shared framework infrastructure. One PHP application, one primary relational database, and one Inertia/React browser application are deployed together. Modules own web entry points and feature orchestration but share Eloquent models/services where the current product intentionally projects the same Planning aggregate.

Primary constraints:

- Planning is the only stored monthly-plan aggregate; Dashboard and Expenses project it.
- Server middleware, validation, authorization, and transactions define trust boundaries; React is a presentation/client-preview layer.
- Public ULIDs are used for route binding and activity identifiers, but current Inertia serialization also exposes internal numeric `id`/`user_id` fields because no explicit resource transform hides them; #61 owns narrowing that boundary.
- There is no implemented public REST API or separate transaction ledger.

## System flow

```text
Browser -> Laravel web middleware/route -> controller + policy/FormRequest
        -> module service -> Eloquent/PostgreSQL + cache/activity history
        -> Inertia response/shared props -> React page
```

For Planning creation:

1. `app/Modules/Planning/routes.php` sends authenticated `POST /planning` requests to `PlanningController::store`.
2. `PlanningStoreRequest` validates the submitted shape; its current rules remain broad string/array validation.
3. The controller passes validated data to `PlanningService::store`.
4. The service reshapes section arrays, assigns the authenticated internal user ID, and persists Planning plus an allowlisted activity entry inside a database transaction.
5. After commit, `PlanningCache` invalidates the authenticated user's collection key.
6. The redirect returns to the Planning index, whose Inertia props come from the cached user-scoped collection.

For direct Planning display, Laravel resolves the public ULID through `HasPublicId` and the controller calls `PlanningPolicy` before returning an Inertia page. Expenses intends the same flow, but its `{expenses}` route parameter does not match controller argument `$expense`; the requested record is not injected and an isolated owner-path request returns 403. Issue #61 owns binding normalization, response serialization, and complete owner/non-owner coverage.

## Layer and module responsibilities

| Layer/module | Owns | Must not own | Evidence |
| --- | --- | --- | --- |
| Laravel bootstrap/middleware | Application startup, trusted hosts, sessions, locale, Inertia shared data | Feature calculations | `bootstrap/app.php`; `HandleInertiaRequests.php` |
| Module routes/controllers | HTTP methods, middleware, model binding, authorization, Inertia response/redirect | Reusable financial calculations or arbitrary persistence | module `routes.php`; controllers |
| Form requests/policies | Request validation and record authorization | Rendering or persistence orchestration | `PlanningStoreRequest.php`; `PlanningPolicy.php` |
| Module services | Reusable application orchestration, transactions, caching coordination | Browser-only presentation | `PlanningService.php`; `ActivityRecorder.php` |
| Eloquent models/migrations | Persistence shape, casts, relationships, public ID behavior | HTTP concerns | `Planning.php`; module migrations |
| Inertia middleware | Shared user/locale/translation/flash props | Page-specific domain queries | `HandleInertiaRequests.php` |
| React pages/layouts | Forms, navigation, previews, and rendering | Authoritative authorization or persisted financial truth | `resources/js/Pages`; `resources/js/Layouts` |
| Operations modules/providers | Diagnostics, retention, scheduling, redaction | Product-domain state | Telescope/activity providers and operations docs |

## Reused patterns

| Pattern | Where | Purpose |
| --- | --- | --- |
| Service-provider module registration | `bootstrap/providers.php`; module providers | One startup registry for routes, migrations, bindings, policies, and commands |
| Constructor injection | controllers and services | Resolve module collaborators through Laravel's container |
| Form Request | Auth/Profile/Planning requests | Keep validation and authorization decisions outside controllers |
| Policy/Gate | `PlanningPolicy`; Planning/Expenses controllers | Enforce record ownership on direct access |
| Database transaction | Planning, registration, profile lifecycle | Keep domain mutation and audit history atomic |
| User-scoped cache-aside | `PlanningCache`; `PlanningService::getAllPlannings` | Reuse five-minute collection reads and invalidate after lifecycle changes |
| Public ID trait | `HasPublicId` on `User`/`Planning` | Generate ULIDs and use them as route keys while persistence continues using numeric keys |
| Inertia shared props | `HandleInertiaRequests` | Provide auth, locale, dictionaries, and flash data consistently |

## Startup order

`public/index.php` loads `bootstrap/app.php`, then Laravel loads providers listed in `bootstrap/providers.php`. `AppServiceProvider` conditionally registers Telescope and shared observers; feature providers register their services before loading routes/migrations/policies in `boot()`. Inertia's root view is `resources/views/app.blade.php`; frontend page discovery is configured in `resources/js/app.tsx`.

## Known architectural risks

- `PlanningService::store` persists browser-supplied totals and `Planning` casts salary as binary float. #57 documents meaning; #63 owns authoritative calculations, precision, constraints, and migration.
- Planning validation does not enforce numeric boundaries, period uniqueness, or deterministic rounding. See #63.
- The Expenses binding name differs from its controller variable. Policy checks exist, but #61 owns binding normalization and complete owner/non-owner coverage.
- Collection reads load every plan without pagination. The cache reduces repeated queries but does not bound payload growth.
- Some Auth controllers still return removed Blade views while the primary UI uses Inertia/React; this causes known backend failures and is owned by #65.
- `PlanningService` is registered as a singleton with a mutable injected `Planning` instance. Current HTTP store flow calls it once, but reusable multi-create flows would need a fresh model boundary.

## Evidence

- `bootstrap/app.php`, `bootstrap/providers.php`
- `app/Modules/Planning/PlanningServiceProvider.php`
- `app/Modules/Planning/Controllers/PlanningController.php`
- `app/Modules/Planning/Services/PlanningService.php`
- `app/Modules/Planning/Policies/PlanningPolicy.php`
- `app/Modules/Expenses/Controllers/ExpensesController.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `resources/js/app.tsx`, `resources/js/Pages/Planning/Create.tsx`
