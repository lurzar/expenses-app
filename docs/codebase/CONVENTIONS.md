# Coding conventions

This reference records conventions visible in committed source and configuration. Where enforcement is incomplete, it says so explicitly.

## Naming rules

| Item | Rule | Example | Evidence |
| --- | --- | --- | --- |
| PHP classes/files | PascalCase class and filename under PSR-4 namespace | `PlanningController`, `PlanningController.php` | `composer.json`; module source |
| PHP methods/variables | camelCase; Laravel lifecycle names where applicable | `getAllPlannings`, `$storedPlanning` | `PlanningService.php` |
| React components/files | PascalCase TSX component and filename | `AppLayout.tsx`, `Planning/Create.tsx` | `resources/js/` |
| TypeScript functions/variables | camelCase | `resolveRouteName`, `translations` | `resources/js/utils/route.ts`; layouts |
| Types/interfaces | PascalCase | `PageProps`, `AppPageProps` | `resources/js/types/index.ts`; layouts |
| Constants | uppercase snake case in PHP; local camelCase is also present in TS | `INDEX_TTL_SECONDS` | `PlanningCache.php` |
| Environment variables | uppercase snake case, grouped by purpose | `TELESCOPE_ENABLED`, `DB_CONNECTION` | `.env.example` |
| Routes | lowercase URI plus dot-separated name | `/planning/{planning}`, `planning.show` | module route files |

## Formatting and linting

- `.editorconfig` requires UTF-8, LF, final newline, four spaces generally, and two spaces for YAML.
- Laravel Pint is the PHP formatter. No project `pint.json` override exists, so the installed Laravel preset/default rules apply.
- TypeScript uses `strict`, `isolatedModules`, `noEmit`, bundler resolution, and consistent filename casing in `tsconfig.json`.
- No ESLint or Prettier configuration is committed. TypeScript checking and Vite compilation are the current frontend static gates.
- Repository-wide Pint currently reports historical findings; #65 owns cleanup and mandatory enforcement. Changed PHP files must pass focused Pint before merge.

```bash
vendor/bin/pint --test path/to/changed.php
npx tsc --noEmit
npm run build
```

## Import and module conventions

- PHP imports follow namespace declarations and are normalized by Pint; current historical files still contain ordering findings.
- Frontend code uses `@/` for imports under `resources/js` and relative imports for nearby bootstrap/entry modules.
- React pages are discovered directly by `import.meta.glob('./Pages/**/*.tsx')`; no barrel-export layer is used for pages.
- Backend feature code belongs in `App\Modules\<Module>\...`; truly shared user, provider, trait, and HTTP middleware code remains under shared `app/` namespaces.
- Route/model references use public model identifiers through `HasPublicId`; persistence relationships use internal numeric keys.

## Error, authorization, and logging conventions

- Form Requests return Laravel validation responses; controllers normally return typed Inertia responses or redirects.
- Record authorization uses policies/Gates. Authentication middleware alone is not treated as ownership authorization.
- Multi-record list queries scope by the authenticated internal user ID.
- Mutations that also write activity history use `DB::transaction` so both succeed or both roll back.
- Activity history accepts only enum-defined events, public ULIDs, and event-specific allowlisted metadata.
- Telescope is conditionally registered, fail-closed outside local, redacts sensitive diagnostic content, and prunes on a configured schedule. See `docs/operations/error-monitoring.md`.
- Application logging uses Laravel channels from `config/logging.php`; no project-wide structured log schema is defined beyond activity-event rules.

## Testing conventions

- Backend tests use Pest's `test()` style. Feature tests live in `tests/Feature/<Area>` and use `RefreshDatabase`; unit tests live in `tests/Unit`.
- Feature behavior is tested through real Laravel routes, Eloquent factories, Inertia assertions, and database state.
- Mockery is used only at explicit collaborator boundaries, such as simulating `ActivityRecorder` failure.
- Security tests create separate users and assert denial plus retained data.
- There is no enforced coverage threshold. #65 owns static analysis, safe database defaults, baseline repair, and CI parity.

## Contribution conventions

- GitHub Issues are the planning source of truth; repository docs are current-behavior truth.
- Use one issue-numbered branch/PR per change and dependency-only stacks.
- Current version work targets the approved temporary version branch created from `v2.x`.
- Use Conventional Commits and `Closes #<issue>` only when the PR fully resolves the issue.
- Full branching, merge, tag, Release, and cleanup rules live in `docs/development/workflow.md`.

## Evidence

- `.editorconfig`, `tsconfig.json`, `composer.json`
- `AGENTS.md`, `docs/development/workflow.md`
- `app/Modules/Planning/Services/PlanningService.php`
- `app/Modules/Planning/Services/PlanningCache.php`
- `app/Modules/ActivityLog/Services/ActivityRecorder.php`
- `tests/Pest.php`, `tests/Feature/Planning/PlanningAuthorizationTest.php`

