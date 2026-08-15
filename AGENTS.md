# Expenses App agent instructions

These instructions apply to the entire repository. This file is the canonical source for repository-wide AI-agent rules. Product-specific instruction files may point here, but must not copy mutable project facts.

## Start with project truth

Before changing the repository:

1. Read [`README.md`](README.md) for the supported product and setup boundary.
2. Read [`docs/README.md`](docs/README.md) to find the canonical document for the task.
3. Inspect the relevant source, configuration, routes, tests, issue, and pull request before relying on prose.
4. Resolve the active release train, integration branch, and PR base from verified GitHub state. Do not assume `main` is the correct base.

Verified source and reproducible commands override stale prose. When they disagree, correct the canonical document or linked issue in the same change.

## Product and architecture boundaries

- Expenses App is a Laravel modular monolith with an Inertia, React, and TypeScript frontend.
- Planning is the central stored monthly aggregate. Dashboard and Expenses are projections of Planning data.
- Do not invent a transaction ledger, public REST API, new role model, or integration without an approved issue.
- Keep business rules and authorization on the server. Client validation improves feedback but is never the only enforcement.
- Preserve financial meaning, precision, ownership boundaries, and lifecycle invariants. Treat changes to calculations, persistence, deletion, and migrations as high risk.
- Keep controllers focused on HTTP orchestration. Put reusable domain behavior in the owning module and follow existing module conventions.
- Reuse existing routes, Inertia props, components, dictionaries, and services before adding parallel abstractions.

## Documentation routing

Use the documentation map in [`docs/README.md`](docs/README.md). In particular:

- product entry point and local setup: [`README.md`](README.md);
- release history: [`CHANGELOG.md`](CHANGELOG.md);
- domain rules: `docs/domain/` when delivered by its linked issue;
- architecture and codebase reference: `docs/codebase/` when delivered;
- interface contracts: `docs/interfaces/` when delivered;
- development and release workflow: `docs/development/` when delivered;
- operations and monitoring: `docs/operations/` when delivered;
- material architecture decisions: `docs/decisions/`.

Describe unmerged behavior as planned and link its issue. Update documentation in the same PR when behavior, setup, interfaces, operations, or validation changes.

## GitHub workflow and traceability

- Use a detailed GitHub issue as the source of truth before broad implementation. Confirm its scope, acceptance criteria, risks, dependencies, release label, and branch plan.
- Name branches with the issue number and intent. Keep commits focused and use Conventional Commit messages.
- Link the implementation PR with `Closes #<issue>` and use `Refs #<issue>` for related work that the PR must not close.
- Use stacked PRs only when changes are genuinely dependent. Each layer must be independently reviewable, have one clear issue, and target the branch immediately below it.
- Review and merge a stack bottom-up. After a lower layer changes or merges, refresh the next layer and re-verify its base and diff.
- Keep release aggregation separate from feature stacks. Do not merge, tag a release, delete a branch, or rewrite shared history without explicit authorization.

## Implementation approach

- Read nearby code and tests before editing; follow repository conventions instead of applying generic Laravel patterns blindly.
- For a feature or bug fix, use a focused red-green-refactor cycle. Add or adjust the smallest test that proves the intended behavior before implementation.
- Avoid unrelated refactors, formatting churn, dependency upgrades, generated files, or speculative abstractions.
- Migrations must preserve existing data or document an explicit migration and rollback path. Never run destructive production or shared-database operations without explicit authorization.
- Keep secrets, credentials, tokens, personal data, production identifiers, and raw diagnostics out of source, tests, issues, PRs, and documentation.
- Monitoring and diagnostic tools must be disabled by default outside approved environments, require server-side authorization, minimize captured sensitive data, and define retention and rollback.

## Validation

Run the smallest relevant checks while developing, then run all configured checks affected by the change. The current baseline commands are:

```bash
composer validate --strict
vendor/bin/pint --test
php artisan test
npx tsc --noEmit
npm run build
composer audit --locked --no-interaction
npm audit --audit-level=high
```

Use an isolated test database. SQLite in memory is acceptable for compatible tests; use PostgreSQL when behavior depends on PostgreSQL semantics. Never point automated tests at development or production data.

Also run configured static analysis, coverage, or security checks when they exist. If a repository-wide check already fails outside the change, report the exact baseline failure and verify that the focused change does not add another failure. Do not silently weaken or bypass a gate.

## Hand-off standard

Before opening or updating a PR:

- inspect the final diff and confirm only intended files changed;
- verify issue, branch, commit, base branch, labels, and closing references from GitHub;
- report checks that passed, checks that failed, and whether failures predate the change;
- call out migrations, configuration, security, financial, operational, and rollback impact;
- leave merge, release tagging, and branch deletion to an explicitly authorized step.
