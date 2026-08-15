# Development and release workflow

This guide gives an Expenses App contributor or release owner one path from a clean checkout to an issue-linked pull request and version release. Verify live GitHub state before acting because branches, pull requests, tags, and quality baselines change.

## Current repository model

As of 2026-08-16:

| Item | Current role |
| --- | --- |
| `main` | GitHub's default branch and the merged v2.0.0 application state. Do not use it as the automatic base for current patch work. |
| `v2.1` | Active integration branch for versions 2.1.0 through 2.1.9. Completed patch-version branches merge here. |
| `v2.0.x` | Temporary version-aggregation branch created from an exact approved `v2.1` commit. |
| `v2.x` | Longer-running v2 release line. PR #37 promotes the completed v2.1.0 milestone from `v2.1`. |
| `v2.0.x-dev` | Lightweight Git tags placed on the `v2.1` merge commit after an approved version release. |
| `v2.1.0-dev` | Lightweight Git tag placed on the `v2.x` merge commit after the approved v2.1.0 promotion. |

PR #37 is not part of an ordinary patch release. It promotes only the 14 resolved v2.1.0 milestone sub-issues; other issues carrying the broad `v2.1` label remain available for versions 2.1.1 through 2.1.9.

The repository currently publishes lightweight patch tags without matching GitHub Release objects. GitHub Releases stop at `v2.0.0-dev`; this is a known distinction, not proof that later tags are missing.

## Tool and runtime matrix

| Surface | Supported or configured value | Source |
| --- | --- | --- |
| Host PHP | PHP 8.4 | `README.md` and `composer.json` |
| Composer platform | PHP 8.4.0 | `composer.json` |
| Sail PHP | PHP 8.4 | `docker-compose.yml` build context |
| Node.js | Node 22 | `README.md` and `.github/workflows/laravel.yml` |
| PostgreSQL | 18.4 Alpine in Sail | `docker-compose.yml` |
| Redis | 8.8.1 Alpine, optional for application cache | `docker-compose.yml` and `.env.example` |
| pgAdmin | 9.16 | `docker-compose.yml` |
| CI database | SQLite file | `.github/workflows/laravel.yml` |
| Default application cache | File | `.env.example` and `config/cache.php` |

The current workflow runs only for pull requests and pushes to `main`. It installs PHP 8.4 and Node 22, builds the frontend, and runs Pest with SQLite. It does not currently validate active `v2.1` or version-branch PRs, Pint, TypeScript as a separate gate, audits, or static analysis. Issue #65 owns that target quality pipeline.

## Set up a clean checkout with Sail

### Prerequisites

- Git and GitHub CLI;
- PHP 8.4 and Composer 2;
- Node.js 22 and npm;
- Docker with Docker Compose; and
- access to `lurzar/expenses-app` when the repository is private.

If an existing Docker volume contains PostgreSQL 17 or earlier data, follow the [PostgreSQL 18 local upgrade guide](../postgresql-18-upgrade.md) before starting PostgreSQL 18.

### Install and start

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

The default application URL is `http://localhost:8080`. `.env.example` sets `APP_PORT=8080`.

Do not copy a real `.env`, database, cache payload, Telescope entry, activity record, or production identifier into a checkout, issue, pull request, or test fixture.

## Use host tools safely

Use the host for Composer, frontend checks, and isolated SQLite tests when its PHP and Node versions match the matrix. The checked-in development database host is `pgsql`, a Sail service name that a host process usually cannot resolve.

Run host tests with an explicit isolated database:

```bash
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test
```

Never run an automated test while its resolved connection can target development or production data. Use Sail when behavior depends on PostgreSQL semantics.

Common setup failures:

- `pgsql` cannot resolve: use Sail or the explicit SQLite test command; do not treat the connection error as a test result.
- Docker reports an incompatible PostgreSQL data directory: stop and use the PostgreSQL 18 upgrade guide.
- Vite cannot find an optional package: remove only the disposable local install state through the approved package workflow, then run `npm ci`; do not edit the lockfile merely to repair one machine.
- cache or log writes fail: check application ownership for `storage` and `bootstrap/cache`; avoid permanent world-writable permissions.

## Verify the live base before branching

Run read-only discovery first:

```bash
git status --short --branch
git remote -v
gh auth status
gh repo view --json nameWithOwner,defaultBranchRef
git branch -a
git tag --sort=-version:refname
gh pr list --state open
```

Choose the base in this order:

1. use the base recorded in the approved issue and release tracker;
2. otherwise use the active version branch when the issue is approved for that release;
3. otherwise use the verified active integration branch, currently `v2.1`; and
4. use `main` only when the issue and repository state explicitly call for the default branch.

Do not branch current issue work from `v2.x`, PR #37, a completed patch branch, or whichever branch happens to be checked out.

## Execute one issue

Before code or documentation, ensure the issue records current context, target outcome, scope, non-goals, dependencies, acceptance criteria, validation, risks, rollback, release label, base, and planned branch.

Use one branch and one pull request per issue:

```text
<type>/<issue-number>-<short-slug>
```

Valid types include `feat`, `fix`, `docs`, `refactor`, `test`, `ci`, `chore`, and `spike`.

For code behavior:

1. add the smallest focused test;
2. run it and confirm it fails for the missing behavior;
3. implement only enough to pass;
4. refactor with the test green; and
5. run affected and aggregate checks.

Use a Conventional Commit:

```text
<type>(<scope>): <imperative outcome>

Refs #<issue-number>
```

Open the pull request against the issue's recorded base. Use `Closes #<issue-number>` only when the PR fully implements that issue. Use `Refs #<number>` for trackers, dependencies, and deferred work.

## Choose direct, independent, or stacked PRs

### Direct PR

Use a direct PR when one issue is independently reviewable. Its base is the approved version or integration branch.

```text
version branch <- issue branch
```

### Independent PRs

Use independent branches when neither change needs the other. Branch both from the same approved base and review them separately.

```text
                <- issue A
version branch
                <- issue B
```

Do not stack independent work merely to create an ordering.

### Stacked PRs

Use a stack when an upper layer genuinely depends on an unmerged lower layer. Common examples are implementation followed by documentation of the exact behavior, or a schema layer followed by code that requires it.

```text
version branch <- implementation <- dependent documentation
```

Each layer must have its own issue, branch, focused diff, validation record, and closing reference. The upper PR targets the branch immediately below it.

## Use native GitHub Stacks

This repository uses the `github/gh-stack` GitHub CLI extension. Confirm availability:

```bash
gh stack --version
```

For existing branches or pull requests, link them bottom to top:

```bash
gh stack link --base <version-branch> --open <bottom-pr> <top-pr>
```

Verify the imported stack and PR bases:

```bash
gh stack checkout <stack-number>
gh stack view --json
gh pr view <bottom-pr> --json baseRefName,headRefName,state,isDraft,mergeable
gh pr view <top-pr> --json baseRefName,headRefName,state,isDraft,mergeable
```

Do not run `gh stack sync` casually. It cascade-rebases and pushes stack branches with `--force-with-lease --atomic`; that rewrites shared branch history and requires explicit authorization.

## Review and merge a stack

Review from the bottom layer upward. Confirm every layer's focused diff, tests, docs, checks, assignee, labels, base, head, and issue relationship.

After explicit merge authorization, native atomic stack merge is the preferred path:

```bash
gh stack merge <stack-number> --squash
```

GitHub merges the approved dependency prefix as one all-or-nothing operation. If one layer cannot merge, none of the selected layers merge.

For a manual fallback:

1. merge the bottom PR into the version branch;
2. update the next branch onto the new version-branch head;
3. retarget the next PR to the version branch;
4. verify that its diff contains only its own layer; and
5. repeat upward.

Never merge the top layer into an already-merged feature branch and assume it reached the version branch. Verify the version branch contains every intended commit or patch.

## Run validation

Use focused checks during development, then run the configured aggregate matrix on the exact release tree:

```bash
composer validate --strict
vendor/bin/pint --test
DB_CONNECTION=sqlite DB_DATABASE=:memory: php artisan test
npx tsc --noEmit
npm run build
composer audit --locked --no-interaction
npm audit --package-lock-only
```

Run changed-file Pint separately so inherited style failures cannot hide new ones. Run configured static analysis only when the repository supplies it; PHPStan/Larastan is not configured yet.

The v2.0.8 baseline recorded 57 passing backend tests and five failures: four missing auth views and one Profile hard-delete expectation that conflicts with the soft-delete model. Repository-wide Pint reported findings in 35 legacy files. Record current counts every time; do not copy these numbers forward as a passing result. Issue #65 owns their resolution.

Browser testing is optional for a change with no browser-visible behavior. Record the omission. For UI changes, add the smallest relevant smoke or interaction check.

## Aggregate a version release

An approved release tracker owns the version scope and decisions.

### Prepare

- [ ] Fetch and verify that local `v2.1` matches `origin/v2.1`.
- [ ] Record the exact base commit in the release tracker.
- [ ] Create and push `v<version>` from that exact commit.
- [ ] Confirm every included issue has the correct version label, base, branch, and PR plan.
- [ ] Keep unrelated issues and PRs out of the version branch.

### Integrate

- [ ] Review and merge dependency stacks with explicit authorization.
- [ ] Merge independent issue PRs into the version branch after their own review.
- [ ] Verify the aggregate file list and commit history against the tracker.
- [ ] Update `CHANGELOG.md` and `config/changelog.php` on the version branch.
- [ ] Run the complete validation matrix on the exact remote release commit.
- [ ] Open a normal version PR from `v<version>` into `v2.1`.

### Complete

- [ ] Verify the version PR's base, head, included issues, checks, mergeability, deployment notes, and rollback.
- [ ] Obtain explicit authorization for the version merge.
- [ ] Verify the resulting `v2.1` commit before tagging.
- [ ] Create and push the lightweight `v<version>-dev` tag only after tag authorization.
- [ ] Verify the remote tag resolves to the exact approved `v2.1` commit.
- [ ] Close completed issues through verified PR relationships or explicit issue updates.
- [ ] Delete only merged issue and version branches covered by explicit cleanup authorization.
- [ ] Leave `v2.1`, `v2.x`, `main`, and PR #37 intact unless separately authorized.

## Own changelogs, tags, and Releases

`CHANGELOG.md` is the repository release history. Follow its Keep a Changelog sections, update `[Unreleased]`, add the dated version, and repair comparison links.

`config/changelog.php` is a short public summary displayed on the login page. Include concise user-facing changes only. Do not copy operational details, vulnerability reproduction, credentials, identifiers, raw diagnostics, or financial data into it.

The version PR body records the exact included PRs, validation, known baseline failures, deployment/rollback notes, and planned tag. It is not a substitute for either changelog.

The recent repository convention uses lightweight `-dev` tags and no new GitHub Release object. Do not create a GitHub Release, mark one latest, change SemVer, or rewrite a tag unless the release tracker explicitly approves it.

Related foundation issues have separate ownership:

- #33 completed the login-page changes summary backed by `config/changelog.php`; release work updates it but does not reopen or reimplement #33.
- #59 completed the documentation source-of-truth map in `docs/README.md`; new canonical documents update that map rather than creating a competing index.
- #65 remains open for deterministic quality gates and inherited failures; this guide records current behavior without claiming that target is complete.

## Roll back and clean up

Before merge, close or update the PR and preserve the branch for correction.

After an issue PR reaches the version branch, revert its commit or patch through a new reviewed PR. Follow any migration, retention, cache, or operational guide attached to that issue.

After a version PR reaches `v2.1`, use a reviewed revert on `v2.1`; do not reset or force-push the integration branch. A published tag must not be moved or deleted without explicit owner approval and a documented recovery plan.

Branch deletion is cleanup, not proof of release. Verify merge state and exact branch targets first. Do not delete branches with unmerged commits, open dependent PRs, or active rollback value.

## Proven stacked-release examples

Version 2.0.7 used two independent two-layer stacks:

- PR #74 implemented #59 on `v2.0.7`; PR #75 documented #62 above it.
- PR #77 implemented #36 on `v2.0.7`; PR #78 documented #73 above it.
- After both stacks reached the version branch, PR #80 merged `v2.0.7` into `v2.1`, and `v2.0.7-dev` marked the merge commit.

Version 2.0.8 used one two-layer stack:

- PR #83 implemented #34 on `v2.0.8`.
- PR #84 documented #81 above PR #83.
- Stack #85 preserved the dependency relationship.
- PR #86 merged the aggregate into `v2.1`, and `v2.0.8-dev` marks commit `0f32c4e61f6e9fb3676a835c06e6c5414d7db570`.

These examples establish the topology. The current release tracker remains the authority for the next version's exact issues, branches, stacks, and authorization checkpoints.
