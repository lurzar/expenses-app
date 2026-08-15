# Dependency updates and audits

This how-to defines the current dependency update, vulnerability-audit, and rollback flow for Composer and npm.

## Required release gates

Committed lock files are the installation authority. Every pull request and release head must pass:

```bash
composer install --no-interaction --prefer-dist --no-progress
npm ci
composer audit --locked --no-interaction
npm audit --package-lock-only
composer check
```

Do not replace `composer install` or `npm ci` with an unlocked update command. Do not use `npm audit fix --force`, suppress an advisory, or regenerate an unrelated lock file merely to obtain a green check.

## Automated update policy

`.github/dependabot.yml` checks Composer and npm weekly on Monday morning in `Asia/Kuala_Lumpur` and targets the active `v2.x` integration line. GitHub activates this file from the default branch, so the same reviewed configuration is installed on `main` through the focused companion PR for #60 and retained on `v2.x` as the active-line source of truth.

- Composer and npm minor/patch updates are separated into runtime and development groups.
- Major updates stay separate and receive a 30-day cooldown.
- Minor updates wait 7 days and patch updates wait 3 days, reducing early-release risk without delaying Dependabot security updates.
- Open version-update PRs are capped at three for Composer and three for npm.
- Dependabot assigns `lurzar` and applies the `dependencies` and `System` labels.

Because GitHub security updates target the default branch independently of a non-default `target-branch`, review any security PR against its actual base first. Port an applicable fix into `v2.x` through an issue-linked branch rather than assuming a default-branch PR reached the active release line.

## Review an update PR

1. Read the release notes and dependency tree; classify runtime versus development exposure.
2. Confirm the PR base, grouped packages, manifest changes, and lockfile diff.
3. Reject unrelated major upgrades, advisory suppressions, and lockfile churn.
4. Run `composer check` on the exact head. Add focused application tests when the dependency affects Laravel, Inertia, React, Vite, authentication, persistence, or serialization behavior.
5. Confirm both locked audits remain clean.
6. Merge only after the normal issue/review authorization; Dependabot does not bypass release workflow.

## Handle an advisory

Patch a fixable runtime/high-severity advisory first, in the smallest compatible dependency group. When no fix exists, the blocking issue and release notes must record:

- affected package and whether exposure is runtime or development-only;
- reachable application surface;
- compensating mitigation;
- named owner; and
- next review date.

Hosted GitHub Actions are intentionally not used. Run and record both local locked audits on every exact issue, stack, version, and release head; absence of hosted execution is never presented as audit evidence.

## Rollback

Revert one logical dependency-update PR at a time and restore both manifest and lockfile together. Rerun the full gate after the revert. Never move a published release tag or weaken audit severity to roll back a package regression; ship a reviewed corrective patch.

## Evidence

- `composer.json`, `composer.lock`
- `package.json`, `package-lock.json`
- `.github/dependabot.yml`
- `docs/codebase/TESTING.md`
