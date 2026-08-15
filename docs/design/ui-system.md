# UI/UX system specification

This document is the approved implementation target for the Expenses App interface. It records product-owner decisions for issue #118 while separating them from the v2.1.2 interface that still exists in source. Runtime adoption belongs to focused v2.1.4+ issues.

## Product experience

The application should feel like a professional banking, currency-exchange, or financial-planning product:

- trustworthy before decorative;
- exact figures before illustration;
- dense enough for comparison, but grouped for quick scanning;
- calm, controlled motion and color;
- useful graphs paired with accessible exact values; and
- explicit about whether a value is planned, calculated, or unavailable.

The product currently stores monthly plans, not bank transactions. Never label a planned allocation as an actual charge, payment, statement, cleared expense, or account balance. The domain reference remains authoritative for financial meaning, and #63 owns server-authoritative calculations.

## Verified current-state audit

As of `v2.1.2-dev`:

- `AppLayout.tsx` has a desktop-only top navigation; its navigation, language, theme, profile, and logout controls disappear below `sm` with no mobile replacement.
- `GuestLayout.tsx` places language/theme controls absolutely and uses a single centered card.
- Dashboard has partial dark-mode classes; most Planning and Expenses surfaces use fixed light backgrounds/text.
- Pages independently choose widths, card grids, header/action placement, empty-state text, and visual states.
- `resources/css/app.css` removes native focus outlines from inputs without defining a global replacement.
- Inter is loaded from Google Fonts with a system fallback; typography roles and numeric presentation are not documented.
- No canonical loading, skeleton, error summary, offline, destructive dialog, reduced-motion, or responsive navigation pattern exists.
- No chart component or chart dependency exists.

These are audit facts, not reasons to preserve the current presentation.

## Information architecture

### Primary destinations

Keep four authenticated destinations:

1. **Dashboard** — overview, current-period context, key planned figures, and trends when enough plans exist.
2. **Planning** — create, browse, inspect, and manage monthly plans.
3. **Expenses** — a read-only allocation projection of Planning until a real ledger is separately implemented.
4. **Profile** — account identity and account-security actions.

Language, theme, and logout are account utilities, not primary destinations. New modules do not enter navigation until their product behavior and permissions exist.

### Responsive application shell

| Width | Approved shell behavior |
| --- | --- |
| `< 768px` (`md`) | Compact top context bar plus fixed bottom navigation. Show Dashboard, Planning, Expenses, and Profile with icon, visible text label, current state, and at least a 44×44 px target. Keep content clear of safe areas and the bottom bar. |
| `768–1023px` | Top context bar plus a closed-by-default 280 px labelled overlay drawer. A visible menu button opens it; focus moves to its heading/first destination, stays within the drawer, and returns to the menu button on close. Escape, overlay click, close button, or destination selection closes it. Drawer state is not persisted across page loads. |
| `>= 1024px` (`lg`) | Persistent 240–256 px labelled left sidebar plus compact top context bar. Main content uses the remaining width; sidebar collapse is optional, not required for first delivery. |

The top context bar owns page title/period, optional breadcrumbs, language, theme, and account menu. Only one navigation landmark and one main landmark should be exposed at a time. Add a keyboard-visible “Skip to main content” link before navigation.

### Page widths

- Financial overview/data pages: fluid, maximum 1440 px, 16 px mobile gutters, 24 px tablet gutters, 32 px desktop gutters.
- Forms and destructive account flows: readable measure, maximum 720 px.
- Guest authentication card: maximum 448 px with controls kept in normal document flow on narrow screens.
- Tables/charts may use full content width, but their headings, legends, and exact-value alternatives remain inside the same panel.

## Semantic visual system

The product owner supplied `#091413`, `#285A48`, `#408A71`, and `#B0E4CC` as a preferred green palette. Use them as semantic anchors, not as a requirement to place all four in every view.

### Color roles

| Token | Light value / approved foreground | Dark value / approved foreground | Approved use |
| --- | --- | --- | --- |
| `canvas` | `#F5FAF8` / `#091413` | `#091413` / `#F5FAF8` | Application background and default text pair |
| `surface` | `#FFFFFF` / `#091413` | `#10211D` / `#F5FAF8` | Cards and primary panels |
| `surface-raised` | `#E8F3EF` / `#091413` | `#17312B` / `#F5FAF8` | Menus, selected panels, secondary cards |
| `text-primary` | `#091413` | `#F5FAF8` | Headings, exact figures, body text on approved surfaces |
| `text-secondary` | `#3E5B52` | `#B0C8BF` | Supporting labels on approved canvas/surfaces |
| `border-quiet` | `#C7D8D2` | `#285A48` | Non-essential grouping/separation only |
| `border-strong` | `#5F7E74` | `#408A71` | Form/control/chart boundaries that must remain perceptible |
| `brand-strong` | `#285A48` / `#FFFFFF` | `#B0E4CC` / `#091413` | Primary actions and strong selected state |
| `brand-hover` | `#1F493B` / `#FFFFFF` | `#C5EFE0` / `#091413` | Primary-action hover/pressed direction |
| `brand-mid` | `#408A71` / `#091413` | `#408A71` / `#091413` | Charts, progress, and large selected decoration; not white normal text |
| `brand-soft` | `#B0E4CC` / `#091413` | `#173B31` / `#B0E4CC` | Active navigation tint, selected rows, chart background |
| `danger-strong` | `#B42318` / `#FFFFFF` | `#FFB4AB` / `#091413` | Destructive buttons and high-emphasis errors |
| `danger-soft` | `#FDECEA` / `#7A271A` | `#4A1714` / `#FFDAD6` | Error panels and negative financial states |
| `warning-strong` | `#8A4B00` / `#FFFFFF` | `#FFD18B` / `#091413` | High-emphasis warning controls/badges |
| `warning-soft` | `#FFF1CC` / `#5C3A00` | `#412B00` / `#FFE2B8` | Warning panels and attention states |
| `focus-ring` | `#285A48` | `#B0E4CC` | Two-pixel-or-greater visible focus indicator with an offset from the control |

Measured text-pair contrast ratios include:

- `#091413` on `#F5FAF8`: 17.76:1;
- `#3E5B52` on white: 7.44:1;
- `#B0C8BF` on `#10211D`: 9.43:1;
- white on `#285A48`: 7.94:1;
- `#B0E4CC` on `#091413`: 13.22:1;
- `#B0E4CC` on `#173B31`: 8.68:1;
- white on `#B42318`: 6.57:1 and `#FFDAD6` on `#4A1714`: 11.41:1;
- white on `#8A4B00`: 6.80:1 and `#FFE2B8` on `#412B00`: 10.71:1; and
- `#091413` on `#408A71`: 4.54:1, while white on `#408A71` is only 4.12:1 and is not approved for normal-size text.

`border-quiet` may only reinforce spacing/grouping that remains understandable without the line. Form inputs, chart axes that encode meaning, and other necessary UI boundaries use `border-strong` or a stronger measured pair.

All final token combinations must meet WCAG 2.2 AA: at least 4.5:1 for normal text, 3:1 for large text and meaningful UI boundaries. Color never carries status, selection, or chart meaning alone.

### Typography and numbers

- Retain Inter with system-ui fallback for the first migration; external font loading must not block usable text.
- Use a restrained type scale: 12/14 px supporting labels, 16 px body, 18–20 px section titles, 24–32 px page titles, and 32–48 px primary financial figures depending on viewport.
- Use `font-variant-numeric: tabular-nums` for money, percentages, dates, comparison columns, and chart axes.
- Use sentence case for headings and controls; avoid all-caps paragraphs. Short eyebrow labels may use uppercase with tracking.
- Do not shrink key figures to fit. Wrap supporting labels and preserve the whole exact value.

### Spacing, shape, elevation, and density

- Use a 4 px spacing base. Preferred steps: 4, 8, 12, 16, 24, 32, 48, and 64 px.
- Standard controls are at least 40 px high on desktop and 44 px on touch layouts.
- Use 8 px radius for controls, 12 px for cards/panels, and 16 px only for prominent overview surfaces.
- Borders provide primary separation. Use subtle elevation for menus/dialogs and at most one quiet card shadow level.
- Data-dense does not mean compressed touch targets or missing whitespace: group related figures tightly, then separate groups clearly.

### Motion

- Use 150–200 ms transitions for hover, focus, menu, and theme-state changes.
- Avoid decorative page motion and continuously animated graphs.
- Respect `prefers-reduced-motion`; remove non-essential animation and retain immediate state feedback.
- Never delay a financial value, error, or destructive result for animation.

## Numbers and financial presentation

### Exact figures

- Summary values use `RM 12,345.67` once #63 establishes authoritative decimal output.
- Until #63, preserve current value semantics and label them as planned values; do not imply new precision guarantees in UI code.
- Use a true minus sign and explicit placement for negative values, for example `−RM 125.50`.
- Percentages show the minimum useful precision and never more than two decimal places.
- Show `RM 0.00` for a real zero; use an em dash plus explanatory text for unavailable/not-calculated values.

### Compact figures

Compact forms such as `RM 12.3k` are allowed only on constrained chart axes or small comparison labels. The exact value must be available in the adjacent table, accessible name, or keyboard/pointer tooltip. Do not compact the primary headline balance.

### Comparison and trend

- State the period and comparison basis, for example “vs Jul 2026 plan”.
- Do not show percentage change when the comparison denominator is zero or unavailable.
- Positive/negative color is accompanied by direction icon/text and a descriptive accessible label.
- Sort periods chronologically regardless of creation order.

## Data visualization

Graphs are a primary part of the approved financial experience, but each must answer a user question and use real available data.

| Visualization | User question | Minimum data | Required alternative |
| --- | --- | --- | --- |
| Allocation composition donut | How is this plan distributed across savings, commitments, and others? | One normalized plan | Exact three-row value/percentage legend or table |
| Income versus allocated horizontal bar | How much planned income is allocated and remaining? | Authoritative income and allocation totals | Exact income, allocated, and remaining figures |
| Savings target progress | How does the savings allocation compare with the target? | Approved target and allocation values from #63 | Exact target, allocated, difference, and percentage text |
| Multi-period grouped bar/line | How have planned income, allocations, or remaining values changed? | At least two chronologically comparable plans | Period-by-period data table |
| Category bars | Which named allocations are largest? | Non-empty section items | Sorted exact-value list |

Rules:

- Never create an “actual spending”, bank-balance, exchange-rate, or transaction trend from Planning data.
- Do not render a misleading chart for one missing/zero dataset; show a purposeful empty explanation.
- Legends and tooltips are keyboard reachable. Tooltips supplement rather than contain the only value.
- Use patterns, labels, shapes, or direct annotations in addition to color.
- A chart panel has a visible title, period/context, short interpretation, exact alternative, and optional details action.
- Simple progress/bars may use semantic HTML/CSS or accessible SVG. A complex chart library requires a focused implementation decision covering bundle size, SSR behavior, keyboard/screen-reader support, maintenance, and license.

## Component contracts

### Application shell

`AppShell` owns responsive navigation, context header, skip link, main landmark, safe-area spacing, theme, locale, and account menu. Pages must not recreate navigation or viewport backgrounds.

### Page header

Contains one `h1`, optional period/context, optional breadcrumb/back path, and actions. On mobile, the primary action remains visible; secondary actions may enter an overflow menu. A destructive action is visually separated from the primary action.

### Metric card

Contains label, exact figure, optional context/delta, and optional small visualization. It must remain understandable without color or the visualization. Loading reserves space; unavailable data is explained rather than displayed as zero.

### Chart panel

Contains a heading, context, visualization, accessible exact-value alternative, and state handling. Charts do not own domain calculations.

### List/table panel

Use cards/list rows on narrow screens and aligned tables when cross-row comparison matters. Column headings, row actions, empty state, sort state, and overflow behavior are explicit. Do not hide essential columns without a labelled disclosure.

### Forms

- Every control has a programmatic label, description where necessary, and an associated inline error.
- Required state is communicated in text, not only with an asterisk/color.
- On submission failure, focus an error summary that links to invalid controls while retaining user input.
- Numeric controls show units/format expectations and do not silently coerce malformed values.
- Primary submit remains distinct from cancel/back and destructive actions.

### Feedback and destructive actions

- Success confirmation uses a polite live region and remains visible long enough to read.
- Errors use an assertive live region only when immediate interruption is necessary.
- Destructive confirmation is a real accessible dialog: descriptive title, consequence, safe initial focus, Escape/cancel, focus return, and explicit action wording such as “Delete August 2026 plan”.
- Do not rely on the browser `confirm()` in the target implementation.

## Interaction and accessibility requirements

- Target WCAG 2.2 AA.
- All functions work with keyboard alone in a logical DOM/focus order.
- Every interactive element has a persistent visible focus indicator of at least 2 px and 3:1 contrast against adjacent colors.
- Restore native outlines until an approved replacement exists; never globally remove focus indication.
- Use native buttons, links, inputs, headings, landmarks, lists, tables, and dialogs before ARIA simulation.
- Icon-only controls require accessible names and visible tooltips; navigation icons always retain visible text labels.
- Touch targets are at least 44×44 px in the mobile shell and are sufficiently separated.
- Text zoom to 200% and reflow at 320 CSS px must not hide content or require two-dimensional scrolling except a genuinely tabular region.
- Status is not conveyed by color alone. Charts use labels/patterns and tables.
- Locale text growth must wrap without clipping; do not size controls to English abbreviations.
- Loading indicators announce intent without repeatedly interrupting assistive technology.

## State contracts

Every data page defines these states before implementation:

| State | Required behavior |
| --- | --- |
| Initial loading | Preserve layout with labelled skeletons or progress; do not show fake zero values. |
| Empty | Explain what is absent, why it matters, and the permitted next action. |
| Partial | Render available panels and identify unavailable calculations without collapsing hierarchy. |
| Validation error | Error summary, associated inline errors, retained inputs, focused first actionable error. |
| Request/system error | Plain-language message, retry when safe, and a non-destructive navigation path. |
| Success | Confirm the completed action and reflect the new state immediately. |
| Destructive confirmation | Name the exact record and consequence; default focus is safe/cancel. |
| No chart data | Explanatory empty state plus exact values if any; never fabricate a series. |

## Current-page migration map

| Page(s) | Target pattern and priority |
| --- | --- |
| Landing | Public marketing shell; clear product boundary and sign-in/register actions; no invented financial metrics. |
| Login/Register | Guest shell, concise form, language/theme/account recovery, trusted security feedback. |
| Forgot/Reset/Verify/Confirm | Same guest/account-security pattern and consistent success/error states. |
| Dashboard | Financial overview: period selector/context, headline planned figures, useful charts with exact alternatives, recent plans/action. |
| Planning index | Search/period-aware plan list, clear create action, compact exact figures, mobile rows/desktop comparison table. |
| Planning create | Focused form layout, section groups, sticky/visible summary where space permits, accessible errors; #63 owns calculations. |
| Planning detail | Financial summary specification owned by #44. |
| Expenses index/detail | Clearly label as plan allocation projection until a ledger exists; reuse Planning summary patterns without “actual spend” language. |
| Profile | Account form and separated danger zone with accessible destructive dialog. |

## Implementation sequence after v2.1.3

1. #63 establishes server-authoritative money values and stable frontend payload meaning.
2. Create a focused UI-foundation issue for semantic tokens, focus restoration, responsive `AppShell`, and primitive components.
3. Implement the #44 Planning summary using authoritative values and accessible chart alternatives.
4. Migrate Dashboard and Expenses projections to the shared summary/chart patterns.
5. Migrate Auth/Profile/forms and remove remaining fixed light-only styles.

Each code PR must include focused component/source tests, TypeScript/build, backend contract tests where props change, exact-head `composer check`, responsive evidence at mobile/tablet/desktop widths, keyboard/focus evidence, light/dark evidence, and an accessibility review. Browser/E2E infrastructure remains a separate decision.

## Evidence

- `tailwind.config.js`, `resources/css/app.css`
- `resources/js/Layouts/AppLayout.tsx`, `GuestLayout.tsx`
- `resources/js/Components/ThemeToggle.tsx`
- `resources/js/Pages/**/*.tsx`
- `resources/js/types/index.ts`
- `docs/domain/expense-planning.md`
- Issues #118, #44, #63, and release tracker #119
