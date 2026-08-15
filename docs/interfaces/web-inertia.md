# Web and Inertia interface reference

This reference catalogs the current browser interface. It describes Laravel web routes and Inertia page contracts; it is not an HTTP API specification.

## Interface model

- Authentication uses Laravel's `web` guard, session cookies, CSRF protection, and redirects.
- Read pages return Inertia responses rooted at `resources/views/app.blade.php` and resolved from `resources/js/Pages/**/*.tsx`.
- Successful mutations redirect to a named web route. Validation errors and old input travel through the session/Inertia error bag.
- Named routes are exposed to TypeScript through Ziggy and resolved by `resources/js/utils/route.ts`; the current language controls pass a stale parameter name and fail against fresh route metadata, as documented below.
- `Planning` and `User` expose public ULIDs, but their current Inertia serialization also includes internal numeric `id`/`user_id` fields; #61 owns narrowing that boundary.
- There is no content-negotiated JSON resource contract for these routes.

## Shared Inertia props

`HandleInertiaRequests::share()` provides every Inertia page with:

| Prop | Shape | Source/notes |
| --- | --- | --- |
| `auth.user` | serialized `User` or `null` | Current session user |
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
| `dashboard` | `Dashboard/Index` | `plannings`: all authenticated-user Planning records |
| `planning.index` | `Planning/Index` | `plannings`: cached authenticated-user Planning records |
| `planning.create` | `Planning/Create` | Shared props only |
| `planning.show` | `Planning/Show` | `planning`: authorized bound Planning record |
| `expenses.index` | `Expenses/Index` | `plannings`: authenticated-user Planning records |
| `expenses.show` | `Expenses/Show` | Intended `planning` projection; an owner request currently returns 403 because binding fails |
| `profile.edit` | `Profile/Edit` | `mustVerifyEmail`, `status` |

Only these ten TSX pages exist. Password reset, password confirmation, and email-verification prompt controllers still call removed `auth.*` Blade views and are known failing paths owned by #65.

## Route catalog

Verified with `php artisan route:list --except-vendor --json` on 2026-08-16: 28 routes, all in the `web` middleware group.

### Public and locale routes

| Method | URI | Name | Controller | Additional middleware/result |
| --- | --- | --- | --- | --- |
| GET | `/` | `landing` | `LandingController@index` | Inertia `Landing/Index` |
| GET | `/language/{language}` | `language` | `LanguageController@index` | Direct route validates allowlisted locale, stores session locale, redirects back; current layout controls pass the wrong key |

### Guest authentication routes

| Method | URI | Name | Controller/action | Result |
| --- | --- | --- | --- | --- |
| GET | `/register` | `register` | `RegisteredUserController@create` | Inertia `Auth/Register` |
| POST | `/register` | — | `RegisteredUserController@store` | Creates/logs in user, redirects |
| GET | `/login` | `login` | `AuthenticatedSessionController@create` | Inertia `Auth/Login` |
| POST | `/login` | — | `AuthenticatedSessionController@store` | Authenticates/regenerates session, redirects |
| GET | `/forgot-password` | `password.request` | `PasswordResetLinkController@create` | Removed Blade view; known failing path |
| POST | `/forgot-password` | `password.email` | `PasswordResetLinkController@store` | Sends configured reset link, redirects back |
| GET | `/reset-password/{token}` | `password.reset` | `NewPasswordController@create` | Removed Blade view; known failing path |
| POST | `/reset-password` | `password.store` | `NewPasswordController@store` | Resets password, redirects to login |

Every route in this group also uses `guest`/`RedirectIfAuthenticated`.

### Authenticated account routes

| Method | URI | Name | Controller/action | Additional middleware/result |
| --- | --- | --- | --- | --- |
| GET | `/verify-email` | `verification.notice` | `EmailVerificationPromptController` | Removed Blade view unless already verified |
| GET | `/verify-email/{id}/{hash}` | `verification.verify` | `VerifyEmailController` | `signed`, `throttle:6,1`; verifies/redirects |
| POST | `/email/verification-notification` | `verification.send` | `EmailVerificationNotificationController@store` | `throttle:6,1`; sends/redirects |
| GET | `/confirm-password` | `password.confirm` | `ConfirmablePasswordController@show` | Removed Blade view |
| POST | `/confirm-password` | — | `ConfirmablePasswordController@store` | Validates password, records session timestamp |
| PUT | `/password` | `password.update` | `PasswordController@update` | Updates password, redirects |
| POST | `/logout` | `logout` | `AuthenticatedSessionController@destroy` | Invalidates session, redirects |
| GET | `/profile` | `profile.edit` | `ProfileController@edit` | Inertia `Profile/Edit` |
| PATCH | `/profile` | `profile.update` | `ProfileController@update` | Validates, persists/activity-logs, redirects |
| DELETE | `/profile` | `profile.destroy` | `ProfileController@destroy` | Password check, soft delete/activity/logout, redirects |

### Planning, Dashboard, and Expenses routes

| Method | URI | Name | Controller/action | Authorization/result |
| --- | --- | --- | --- | --- |
| GET | `/dashboard` | `dashboard` | `DashboardController@index` | `verified`; Inertia collection projection |
| GET | `/planning` | `planning.index` | `PlanningController@index` | User-scoped collection |
| GET | `/planning/create` | `planning.create` | `PlanningController@create` | Creation form |
| POST | `/planning` | `planning.store` | `PlanningController@store` | Authenticated create; current request authorizes broadly |
| GET | `/planning/{planning}` | `planning.show` | `PlanningController@show` | Public-ULID binding plus `PlanningPolicy::view` |
| DELETE | `/planning/{planning}` | `planning.destroy` | `PlanningController@destroy` | Public-ULID binding plus `PlanningPolicy::delete` |
| GET | `/expenses` | `expenses.index` | `ExpensesController@index` | User-scoped Planning projection |
| GET | `/expenses/{expenses}` | `expenses.show` | `ExpensesController@show` | Currently fails owner access with 403 before the intended projection |

## Binding and authorization constraints

- `Planning::getRouteKeyName()` resolves to the public `planning_id` through `HasPublicId`; URLs must not expose the internal numeric primary key.
- Planning collection queries scope by `Auth::id()`.
- Direct Planning show/delete actions call the registered policy.
- The Expenses detail route parameter is `{expenses}` while the controller expects `Planning $expense`. Laravel does not inject the requested bound record into that argument; the policy receives an unbound model and an isolated owner-path request returns 403. #61 owns normalization and owner/non-owner success/failure coverage.
- Session authentication is not sufficient authorization for a specific Planning record; every new direct-record route must call a policy or use scoped binding.

## Language route contract gap

The live route and fresh `@routes` output require `{language}`. `AppLayout.tsx` and `GuestLayout.tsx` currently call `route('language', { lang: 'en' | 'my' })`, while the committed `resources/js/ziggy.js` still contains stale `{lang?}` metadata. With current generated Ziggy configuration, the controls throw that the `language` parameter is required instead of navigating.

Issue #103 owns normalizing the Laravel/Ziggy/layout parameter, removing or regenerating stale metadata, and adding guest/authenticated regression coverage in v2.1.2.

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
