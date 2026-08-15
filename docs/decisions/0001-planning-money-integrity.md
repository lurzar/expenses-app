# ADR 0001: Server-authoritative Planning money

- Status: Accepted for v2.1.4
- Date: 2026-08-16
- Owners: Planning/domain maintainers
- Issue: [#63](https://github.com/lurzar/expenses-app/issues/63)

## Context

Planning previously stored monthly income as a binary float, accepted browser-built totals, discarded the saving rate, used unrestricted string periods, and allowed duplicate active owner/period rows. Dashboard, Planning, and Expenses then recomputed different meanings in JavaScript or model accessors. That made cent-level behavior, tamper resistance, and graph inputs non-deterministic.

## Decision

1. Accept MYR inputs only as plain non-negative decimals with no more than two fractional digits and a maximum of `999999999999.99`.
2. Normalize calculations to signed 64-bit integer sen. Persist income as `DECIMAL(14,2)` and every section/total JSON amount as a canonical two-decimal string. Eloquent uses `decimal:2`, never `float`, for authoritative money.
3. Persist saving rate as `DECIMAL(5,2)`, normalize it to integer basis points from `0` through `10,000`, and round `income_sen × rate_basis_points / 10,000` half up once to the nearest sen.
4. Calculate `target_savings`, `savings`, `commitments`, `others`, `spending`, `allocated`, and `balance` on the server. Submitted `totals` are excluded. New plans with negative balance are rejected.
5. Persist month as integer `1`–`12` and year as integer `2000`–`2100`. Enforce one non-deleted plan for each owner/month/year through request validation and a partial unique index.
6. Monthly income means the combined net/available income for this plan. A savings allocation below its target is informational rather than blocking.

## Consequences

- Dashboard, Planning, and Expenses receive the same exact decimal-string payload and may only convert values at a tested visualization boundary.
- React retains a BigInt-based preview for responsiveness, but the saved response remains authoritative.
- Existing plan rows are normalized during migration: month names become integers, legacy floats/JSON values become two-decimal strings, saving rate is derived from the prior target/income, and canonical totals are recomputed from sections.
- Legacy active duplicate periods deliberately stop migration so an operator can resolve them rather than silently deleting or merging records.
- Legacy rows whose allocations exceed income retain a signed negative balance during normalization; new submissions cannot create that state.
- Multi-currency, individual income sources, a transaction ledger, recurring commitments, and history pagination remain separate decisions.

## Deployment and rollback

The migration accepts exact English month names or integer month values from 1 through 12. Ambiguous numeric forms such as decimals or scientific notation stop the migration and identify the Planning public ID for operator resolution. A legacy target-savings value above income, including a nonzero target with zero income, also stops with the affected public ID instead of being silently clamped or rewritten. After deriving the nearest two-decimal saving rate, the migration recomputes the target and requires exact sen equality; targets that cannot be represented without loss stop for operator resolution.

Back up `plannings` before migration and compare row counts, public IDs, owner IDs, periods, section names/amounts, and canonical totals afterward. The migration is reversible to the former string-period/float-income schema, although canonical totals/sections remain normalized. A rollback therefore restores schema compatibility, not lost binary-float artifacts. If the duplicate-period preflight fails, resolve the owner/period conflict from a verified backup before retrying.

Run the exact-head test suite, representative migration up/down test, PostgreSQL SQL review, and application smoke validation before release. After an immutable release tag is published, correct defects in a new patch rather than moving the tag.
