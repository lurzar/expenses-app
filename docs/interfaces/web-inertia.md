# Web and Inertia interface reference

This reference catalogs the current browser interface. It describes Laravel web routes and Inertia page contracts; it is not an HTTP API specification.

## Interface model

- Authentication uses Laravel's `web` guard, session cookies, CSRF protection, and redirects.
- Read pages return Inertia responses rooted at `resources/views/app.blade.php` and resolved from `resources/js/Pages/**/*.tsx`.
- Successful mutations redirect to a named web route. Validation errors and old input travel through the session/Inertia error bag.
- Named routes are exposed to TypeScript through Ziggy and resolved by `resources/js/utils/route.ts`; generated route metadata and layout callers use the live Laravel parameter names.
- Planning pages receive explicit `PlanningData` payloads keyed by public `planning_id`; internal numeric Planning and owner IDs are not serialized. Shared authentication uses `AuthenticatedUserData` keyed by public `user_id` and omits the numeric user primary key.
- There is no content-negotiated JSON resource contract for these routes.

## Shared Inertia props

`HandleInertiaRequests::share()` provides every Inertia page with:

| Prop | Shape | Source/notes |
| --- | --- | --- |
| `auth.user` | explicit user payload or `null` | Public `user_id`, name, email, verification timestamp, and record timestamps; no numeric primary key |
| `auth.capabilities.access_admin` | boolean | Server-computed `admin.access` result for navigation/presentation; not an authorization credential |
| `locale` | string | Active Laravel locale |
| `translations` | record keyed by translation filename | Every PHP dictionary under the active `lang/<locale>` directory |
| `flash.message` | string or `null` | Session `message` |
| `flash.success` | string or `null` | Session `success` |
| `flash.error` | string or `null` | Session `error` |
| `errors` | field error map | Inertia/Laravel parent middleware |

`resources/js/types/index.ts::PageProps` is the handwritten frontend base type. It is useful but not generated from PHP and can drift; the current Planning totals mismatch is documented in [`../domain/expense-planning.md`](../domain/expense-planning.md).

## Current page contracts

| Route | Inertia page | Page-specific props |
| --- | --- | --- |
| `landing` | `Landing/Index` | Shared props only |
| `login` | `Auth/Login` | `changelog`: newest configured release summary or `null` |
| `register` | `Auth/Register` | Shared props only |
| `password.request` | `Auth/ForgotPassword` | `status` |
| `password.reset` | `Auth/ResetPassword` | reset token and email |
| `verification.notice` | `Auth/VerifyEmail` | `status` |
| `password.confirm` | `Auth/ConfirmPassword` | Shared props only |
| `dashboard` | `Dashboard/Index` | `plannings`: all authenticated-user Planning records |
| `planning.index` | `Planning/Index` | `plannings`: cached authenticated-user Planning records |
| `planning.create` | `Planning/Create` | Shared props only |
| `planning.show` | `Planning/Show` | `planning`: authorized public-ID Planning payload |
| `expenses.index` | `Expenses/Index` | `plannings`: authenticated-user Planning records |
| `expenses.show` | `Expenses/Show` | `planning`: authorized public-ID Planning payload |
| `profile.edit` | `Profile/Edit` | `mustVerifyEmail`, `status` |

These fourteen TSX pages cover the current rendered route contracts.

## Route catalog

Verified with `php artisan route:list --except-vendor --json` on 2026-08-16: 28 routes, all in the `web` middleware group.

### Public and locale routes

| Method | URI | Name | Controller | Additional middleware/result |
| --- | --- | --- | --- | --- |
| GET | `/` | `landing` | `LandingController@index` | Inertia `Landing/Index` |
| GET | `/language/{language}` | `language` | `LanguageController@index` | Validates the allowlisted locale, stores it in session, and redirects back; both layouts generate this required parameter |

### Guest authentication routes

| Method | URI | Name | Controller/action | Result |
| --- | --- | --- | --- | --- |
| GET | `/register` | `register` | `RegisteredUserController@create` | Inertia `Auth/Register` |
| POST | `/register` | — | `RegisteredUserController@store` | Creates/logs in user, redirects |
| GET | `/login` | `login` | `AuthenticatedSessionController@create` | Inertia `Auth/Login` |
| POST | `/login` | — | `AuthenticatedSessionController@store` | Authenticates/regenerates session, redirects |
| GET | `/forgot-password` | `password.request` | `PasswordResetLinkController@create` | Inertia `Auth/ForgotPassword` |
| POST | `/forgot-password` | `password.email` | `PasswordResetLinkController@store` | Sends configured reset link, redirects back |
| GET | `/reset-password/{token}` | `password.reset` | `NewPasswordController@create` | Inertia `Auth/ResetPassword` |
| POST | `/reset-password` | `password.store` | `NewPasswordController@store` | Resets password, redirects to login |

Every route in this group also uses `guest`/`RedirectIfAuthenticated`.

### Authenticated account routes

| Method | URI | Name | Controller/action | Additional middleware/result |
| --- | --- | --- | --- | --- |
| GET | `/verify-email` | `verification.notice` | `EmailVerificationPromptController` | Inertia `Auth/VerifyEmail` unless already verified |
| GET | `/verify-email/{id}/{hash}` | `verification.verify` | `VerifyEmailController` | `signed`, `throttle:6,1`; verifies/redirects |
| POST | `/email/verification-notification` | `verification.send` | `EmailVerificationNotificationController@store` | `throttle:6,1`; sends/redirects |
| GET | `/confirm-password` | `password.confirm` | `ConfirmablePasswordController@show` | Inertia `Auth/ConfirmPassword` |
| POST | `/confirm-password` | — | `ConfirmablePasswordController@store` | Validates password, records session timestamp |
| PUT | `/password` | `password.update` | `PasswordController@update` | Updates password, redirects |
| POST | `/logout` | `logout` | `AuthenticatedSessionController@destroy` | Invalidates session, redirects |
| GET | `/profile` | `profile.edit` | `ProfileController@edit` | Inertia `Profile/Edit` |
| PATCH | `/profile` | `profile.update` | `ProfileController@update` | Validates, persists/activity-logs, redirects |
| DELETE | `/profile` | `profile.destroy` | `ProfileController@destroy` | Password check, soft delete/activity/logout, redirects |

### Planning, Dashboard, and Expenses routes

| Method | URI | Name | Controller/action | Authorization/result |
| --- | --- | --- | --- | --- |
| GET | `/dashboard` | `dashboard` | `DashboardController@index` | `verified`; `planning.view` through `PlanningPolicy::viewAny`; owner-scoped projection |
| GET | `/planning` | `planning.index` | `PlanningController@index` | `planning.view` through `PlanningPolicy::viewAny`; owner-scoped collection |
| GET | `/planning/create` | `planning.create` | `PlanningController@create` | `planning.create` through `PlanningPolicy::create` |
| POST | `/planning` | `planning.store` | `PlanningController@store` | `planning.create` through `PlanningStoreRequest::authorize` |
| GET | `/planning/{planning}` | `planning.show` | `PlanningController@show` | Public-ULID binding plus `planning.view` and owner policy |
| DELETE | `/planning/{planning}` | `planning.destroy` | `PlanningController@destroy` | Public-ULID binding plus `planning.delete` and owner policy |
| GET | `/expenses` | `expenses.index` | `ExpensesController@index` | `planning.view` through `PlanningPolicy::viewAny`; owner-scoped projection |
| GET | `/expenses/{expense}` | `expenses.show` | `ExpensesController@show` | Public-ULID binding plus `planning.view` and owner policy |

## Binding and authorization constraints

- `Planning::getRouteKeyName()` resolves to the public `planning_id` through `HasPublicId`; URLs must not expose the internal numeric primary key.
- Planning collection queries scope by `Auth::id()`.
- Direct Planning show/delete actions call the registered policy.
- Expenses uses the same singular route/controller argument name, public-ULID binding, and `PlanningPolicy::view` owner check as Planning detail.
- Planning, Expenses, and Dashboard serialize Planning through `PlanningData`; direct Eloquent model serialization is not an Inertia contract.
- Session authentication is not sufficient authorization for a specific Planning record; every new direct-record route must call a policy or use scoped binding.
- Shared capability booleans are presentation hints only. Direct requests still pass through Laravel Gate, policies, and Form Request authorization.
- Shared props never include role names, permission lists, package models, pivots, or authorization database identifiers.

## Language route contract gap

The live route, committed Ziggy metadata, and both layouts use the required `{language}` parameter. `resources/js/language-route.test.ts` proves EN/MY URL generation, missing-parameter rejection, and both layout call sites; `LanguageSecurityTest` proves the server allowlist and session redirect behavior.

## Change rules

When a web interface changes, the same PR must update:

1. the module route and controller/request/policy;
2. the Inertia page and handwritten TypeScript props;
3. owner and non-owner feature coverage where a record is addressed;
4. this catalog when method, URI, middleware, binding, page, or props change.

Do not add speculative JSON behavior to a web route. A public/machine interface follows [`api-governance.md`](api-governance.md).

## Evidence

- `php artisan route:list --except-vendor --json` (28 routes on 2026-08-16)
- `app/Modules/*/routes.php`
- `app/Modules/*/Controllers/*.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Modules/Planning/Policies/PlanningPolicy.php`
- `app/Traits/HasPublicId.php`
- `resources/js/app.tsx`
- `resources/js/Pages/`
- `resources/js/types/index.ts`
- `resources/js/utils/route.ts`
