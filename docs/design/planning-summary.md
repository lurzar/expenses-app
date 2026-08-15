# Planning summary layout specification

This is the approved implementation target for issue #44. It applies the financial UI system in [`ui-system.md`](ui-system.md) to Dashboard, Planning detail, and Expenses detail without changing financial meaning or pretending that planned allocations are transactions.

Issue #124 implements this target in v2.1.4 with shared exact-value summary, chart-panel, allocation-section, planned-trend, and delete-dialog components. Presentation ratios and deltas use one tested BigInt/sen boundary; page components consume the canonical #63 payload and do not redefine financial formulas.

## Product questions and hierarchy

The summary must answer these questions in order:

1. **What remains from this monthly plan?**
2. **What income amount is this plan based on?**
3. **How much is allocated to savings, and how does it compare with the target?**
4. **How much is allocated to commitments?**
5. **How much is allocated to other purposes?**
6. **How do those allocations compose the total and compare with income?**
7. **How does this plan compare with other available periods?**
8. **Which named allocations contribute most to each section?**

The initial summary flow provides period context, remaining planned balance, the principal figures in that order, and at least one useful composition view before detailed item lists. At desktop widths, the first viewport should include the period, hero, principal figures, and a useful composition view when normal text sizing permits it. Mobile, tablet, and 200% zoom do not have a first-screen constraint; readability and the approved order take priority.

## Financial meaning boundary

### Implemented v2.1.4 money and presentation behavior

- The server supplies exact decimal-string income, saving rate, sections, and canonical totals to every projection.
- `Planning::spending` reads canonical commitments-plus-others spending.
- Dashboard explicitly labels the first server-returned record as the selected/latest available plan; there is no interactive period selector in v2.1.4.
- The create page uses one BigInt/sen preview helper, excludes submitted totals, and persists server-authoritative values.
- Dashboard, Planning detail, and Expenses projection share the same exact-value summary, accessible chart alternatives, unavailable/legacy states, and directional target/over-allocation language.

The #63 values and formulas remain authoritative. The shared presentation layer computes only display ratios, differences, and visual magnitudes from that payload; it does not add financial rules.

### Authoritative values available to runtime implementation

Issue #63 provides server-authoritative values and documented rounding for:

- monthly income;
- savings target;
- savings allocated;
- commitments allocated;
- other allocated;
- total allocated;
- spending, using the approved domain meaning;
- remaining planned balance; and
- any percentages/deltas used by the interface.

The UI may format and visualize those values but must not independently decide their financial meaning. The financial summary consumes the shared exact payload and may convert only at the tested graph-geometry boundary.

## Canonical labels

| Concept | Primary label | Supporting explanation |
| --- | --- | --- |
| Plan period | Month and year, for example `August 2026` | Always visible near the page title and chart context |
| Remaining | `Remaining planned balance` | Income minus all approved planned allocations; a negative value is a defensive legacy, migration, or pre-submit validation state rather than an approved persisted target after #63 |
| Income | `Monthly income` | Current product input labelled Salary may remain as supporting legacy copy during migration |
| Total allocated | `Total planned allocation` | Savings plus commitments plus others |
| Savings target | `Savings target` | Advisory target approved/calculated by #63 |
| Savings allocated | `Savings allocation` | Sum of named savings items |
| Commitments | `Commitments` | Planned contractual/recurring outflow |
| Others | `Other allocations` | Planned discretionary/uncategorized outflow |
| Spending | `Planned spending` | Commitments plus others, enforced by #63 |

Never use `Actual spending`, `Transactions`, `Paid`, `Cleared`, `Statement balance`, or `Bank balance` for Planning data.

## Page roles

### Dashboard

Dashboard is a cross-period overview, not a duplicate detail page.

- Show the selected/latest available plan period explicitly. Do not call it “this month” unless selection logic proves it is the current calendar month.
- Lead with remaining planned balance and a concise four-to-six-figure grid.
- Show an allocation composition chart and savings-target progress for the selected plan.
- Show a multi-period planned trend only with at least two comparable plans.
- Provide a visible action to open the full Planning detail and a secondary action to create the missing current-period plan when appropriate.

### Planning detail

Planning detail is the canonical complete summary.

- Show every principal figure and both core single-period graphs.
- Show exact section/item breakdowns and the full source values behind charts.
- Primary action: context-dependent “Edit plan” only when an edit workflow actually exists; until then, do not render a disabled/fake action.
- Secondary action: “View expense projection”.
- Destructive action: “Delete plan” in a separated overflow/danger area with an accessible confirmation dialog.

### Expenses detail

Expenses detail remains a read-only projection of Planning.

- Reuse the period, headline figures, composition, income-versus-allocated, and item breakdown patterns.
- Page title and explanation must say `Expense projection` or `Planned allocation breakdown`, not imply a transaction ledger.
- Do not show Planning deletion here.
- Provide a clear route back to the full plan.

## Figure hierarchy

Use these levels:

1. **Hero figure:** Remaining planned balance, exact and un-compacted.
2. **First supporting figure:** Monthly income.
3. **Second supporting figure:** Savings allocation, with savings target and its difference/percentage adjacent as context when authoritative.
4. **Third supporting figure:** Commitments.
5. **Fourth supporting figure:** Other allocations.
6. **Roll-up/context figures:** Total planned allocation, planned spending, and comparison period when authoritative. These must not visually outrank or precede savings, commitments, or other allocations in the reading order.

Each figure includes:

- a persistent label;
- exact `RM` value using tabular numerals;
- optional short context/delta with period basis;
- status text/icon when negative or over-allocated; and
- no invented zero while data is loading/unavailable.

For legacy/migration data or a pre-submit form preview, negative remaining is a clear “Over allocated by RM …” error state. It uses danger semantics plus text and icon, not red alone. This defensive presentation does not approve persisting a negative target plan: #63 is expected to reject that target state.

## Approved visualizations

### 1. Allocation composition

**Form:** donut/ring at medium/large widths; labelled 100% stacked bar is acceptable on narrow screens when clearer.

**Segments:** Savings allocation, Commitments, Other allocations.

**Required adjacent legend/table:** category, exact RM value, and percentage. Sort remains semantically fixed rather than by size so colors/order stay predictable.

**Empty behavior:** when all values are zero, do not render a decorative empty ring. Show “No allocations added” and the exact zeros/next action.

### 2. Income versus allocated and remaining

**Form:** horizontal comparison/progress bar.

- Base represents monthly income.
- Allocated portion represents total planned allocation.
- Remaining portion represents non-allocated income.
- When allocated exceeds income, do not extend a misleading percentage scale silently; show a separate over-allocation segment/marker and exact overage text.

Exact income, allocated, remaining/overage, and percentage values are listed beside or below the bar.

When monthly income is zero, the income-based percentage is unavailable and no division is attempted. Keep the exact `RM 0.00` income and exact allocation/remaining or overage visible. If allocation is also zero, label the comparison as not yet available; if allocation is greater than zero, show the exact over-allocation and explain that a percentage cannot be calculated from zero income.

### 3. Savings target progress

**Form:** progress bar or compact radial progress using the target and allocation provided by #63.

Display exact target, allocation, difference, and percentage. Values above target may exceed 100% numerically; the visual track caps at 100% and shows an explicit above-target indicator rather than hiding the excess.

When the savings target is zero, its percentage is unavailable and the track must not imply 0% or 100%. If allocation is also zero, show both exact zeros and “No savings target set”. If allocation is greater than zero, show the exact allocation/difference and “Target is zero; percentage unavailable”.

### 4. Multi-period planned trend

**Form:** grouped bar for discrete monthly comparisons; a line may be used only when chronological continuity and missing periods are clearly represented.

Default series should be limited to three meaningful values to avoid a noisy financial chart, for example income, total allocated, and remaining. Users may reveal savings/commitments/others through an accessible series control.

Requirements:

- at least two comparable periods;
- chronological month/year axis;
- selected period announced;
- exact period-by-period table;
- no interpolation across missing plans; and
- no “actual” or bank-like language.

### 5. Category magnitude bars

Within each section, use optional horizontal micro-bars to make large allocations scannable. Item name and exact value remain primary; bars are decorative/redundant and hidden from assistive technology when they add no extra meaning.

## Layout behavior

### Mobile (`< 768px`)

Order:

1. compact page header and period;
2. hero remaining figure;
3. monthly income;
4. savings allocation with savings target context;
5. commitments;
6. other allocations;
7. total planned allocation and other roll-up context;
8. allocation composition and exact legend;
9. income-versus-allocated;
10. savings-target visualization;
11. trend chart when eligible;
12. section item lists;
13. secondary and destructive actions.

Supporting figures may use a two-column grid only when that visual placement preserves the same DOM/reading order and remains readable at 320 CSS px; otherwise they stack.

Charts stack vertically and never require horizontal page scrolling. Bottom-nav safe-area padding remains reserved. Sticky financial cards are not required; avoid consuming scarce vertical space.

### Tablet (`768–1023px`)

- Hero figure spans the content width.
- Primary/supporting figures use a responsive two- or three-column grid.
- Composition and income/allocated charts may form a balanced two-column row when legends fit.
- Item sections remain full-width or two-column only when names/values do not truncate.

### Desktop (`>= 1024px`)

Use a 12-column content grid after the persistent sidebar:

- hero/context block: 7–8 columns;
- primary figure panel: remaining 4–5 columns;
- allocation composition: 5 columns;
- income/allocated and savings target: 7 columns, stacked internally;
- multi-period trend: full 12 columns;
- three allocation sections: either 4 columns each for short lists or one full-width comparison table for longer lists.

The layout may be data-dense, but the reading/DOM order remains the same as mobile hierarchy.

## Component anatomy

### `PlanSummaryHeader`

- one `h1`;
- period and “monthly plan” context;
- back/breadcrumb behavior;
- primary/secondary actions; and
- separated overflow/danger action.

### `FinancialHero`

- `Remaining planned balance` label;
- exact value;
- status/context line;
- optional selected-period comparison; and
- accessible description for negative/over-allocated state.

### `MetricGrid` / `MetricCard`

Accepts authoritative label/value/context and never performs a domain calculation. Loading, unavailable, positive, warning, and negative states use semantic tokens.

### `FinancialChartPanel`

Owns heading/context, visualization, keyboard-reachable legend/control, exact-value table, empty/error state, and optional details disclosure. It does not own formulas.

### `AllocationSection`

Contains section heading, exact section total, item count, list/table of name/value pairs, optional redundant magnitude bars, and purposeful empty state.

### `DeletePlanDialog`

Names the period, says the record is removed from Planning and its projections, focuses the safe action initially, supports Escape/cancel, and returns focus to the trigger.

## States

| State | Planning summary behavior |
| --- | --- |
| No plans | Explain monthly planning and offer `Create a plan`; show no zero-filled charts. |
| Requested plan missing | Standard not-found behavior with route back to Planning; never expose another owner’s existence/data. |
| Loading | Current Inertia initial/full visits use the global progress indicator and do not mount the page before authoritative props arrive, so no fake figures are rendered. Labelled skeletons are required if later asynchronous panels render independently. |
| Partial/legacy data | Show available exact values; mark target/comparison unavailable; do not infer missing values. |
| Zero allocations | Income remains visible; remaining equals authoritative result; composition chart becomes a purposeful empty state. |
| Negative remaining | For legacy/migration data or a pre-submit validation preview only, hero and income/allocated panel state exact over-allocation with text/icon/danger role; #63 rejects this as a new persisted target plan. |
| One historical period | Hide trend visualization and explain that another plan is needed for comparison only when useful. |
| Chart failure | Exact-value table/list remains visible; chart error does not hide financial data. |
| Delete pending | Disable duplicate confirmation, announce progress, retain safe cancellation only if request is cancellable. |
| Delete success/failure | Announce result; on success return to Planning list, on failure keep context and restore focus. |

## Accessibility and interaction

- DOM/heading order follows the mobile hierarchy even when desktop grid placement changes.
- Primary figure changes are announced politely only after a user action; initial page values are read normally.
- Chart legends are real buttons only when they change series; otherwise use list/table semantics.
- Pointer tooltips are also keyboard reachable and never contain the only exact value.
- Do not put a canvas-only chart in the accessibility tree without a meaningful alternative.
- Section item names wrap; exact values remain associated and use tabular numerals.
- Every action has a visible label. An overflow icon has an accessible name such as “More actions for August 2026 plan”.
- Focus is visible, logical, and restored after dialogs/menus.
- Language expansion, 200% zoom, dark mode, reduced motion, and 320 CSS px reflow are required review states.

## Data and formatting contract supplied by #63

The presentation payload contains authoritative decimal strings for the canonical values. The UI converts only for chart geometry through one tested boundary and retains exact display strings. It does not parse persisted floats independently in each component.

The payload should identify the plan by public ULID and include an explicit period. Named item amounts remain tied to their section. Any percentage/delta required by charts is either server-provided or calculated through one approved shared client helper from authoritative integer/fixed-precision values.

## Implementation and review slices

1. **Money authority (#63, complete):** persistence, validation, calculations, migration, and stable presentation values.
2. **UI foundation (#123):** semantic tokens, focus restoration, responsive application shell, primitives, number formatter, and chart accessibility wrapper.
3. **Planning summary:** hero, metrics, single-period charts, sections, states, and delete dialog.
4. **Dashboard:** explicit selected period and eligible multi-period trend.
5. **Expenses projection:** reuse summary components with accurate projection language and no destructive controls.

Each slice needs focused tests, types/build, exact-head `composer check`, mobile/tablet/desktop evidence, light/dark evidence, keyboard/focus evidence, and exact-value chart alternatives. Do not combine a money migration and broad shell redesign into one unreviewable PR.

## Evidence

- `resources/js/Pages/Planning/Show.tsx`
- `resources/js/Pages/Expenses/Show.tsx`
- `resources/js/Pages/Dashboard/Index.tsx`
- `resources/js/Pages/Planning/Index.tsx`, `Create.tsx`
- `resources/js/types/index.ts`
- `app/Modules/Planning/Models/Planning.php`
- `docs/domain/expense-planning.md`
- `docs/design/ui-system.md`
- Issues #44, #63, #118, and release tracker #119
