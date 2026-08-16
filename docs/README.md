# Expenses App documentation

This page maps each kind of project knowledge to one canonical source. Repository documentation describes approved, current behavior. GitHub Issues describe planned work until its implementation and documentation merge.

## Documentation map

| Topic | Canonical source | Status | Owner and update trigger |
| --- | --- | --- | --- |
| Product overview and quick start | [`README.md`](../README.md) | Current | Maintainers; update when supported setup or user-visible capabilities change. |
| Documentation map and policy | [`docs/README.md`](README.md) | Current | Maintainers; update when a documentation category, owner, or canonical location changes. |
| Release history | [`CHANGELOG.md`](../CHANGELOG.md) | Current from 2.0.6 | Release owner; update on every version aggregate. |
| PostgreSQL 18 local upgrade | [`docs/postgresql-18-upgrade.md`](postgresql-18-upgrade.md) | Current | Platform owner; update when the supported PostgreSQL image or migration process changes. |
| Domain language and financial rules | [`docs/domain/expense-planning.md`](domain/expense-planning.md) and [`ADR 0001`](decisions/0001-planning-money-integrity.md) | Current authoritative Planning contract | Domain owner; update when financial meaning, lifecycle, precision, or invariants change. |
| UI/UX system, accessibility, responsive shell, and data visualization | [`UI system`](design/ui-system.md) and [`Planning summary`](design/planning-summary.md) | Shared shell/tokens implemented by #123; Planning projections implemented by #124 | Product/design owner; update when navigation, semantic tokens, accessibility, responsive behavior, component contracts, visualization policy, or Planning summary hierarchy changes. |
| Authorization and Admin contract | [`Authorization contract`](codebase/AUTHORIZATION.md) and [`ADR 0002`](decisions/0002-modular-laravel-authorization.md) | Authorization foundation implemented by #13 and super-admin lifecycle implemented by #132; Admin UI remains issue-backed work | Authorization/system owner; update when roles, permissions, policies, ownership, Admin access, capability props, privileged auditing, or provisioning changes. |
| Stack, structure, architecture, conventions, integrations, testing, and concerns | [`STACK`](codebase/STACK.md), [`STRUCTURE`](codebase/STRUCTURE.md), [`ARCHITECTURE`](codebase/ARCHITECTURE.md), [`CONVENTIONS`](codebase/CONVENTIONS.md), [`INTEGRATIONS`](codebase/INTEGRATIONS.md), [`TESTING`](codebase/TESTING.md), [`CONCERNS`](codebase/CONCERNS.md) | Current | Technical owner; update when architecture, module boundaries, integrations, supported tooling, testing, or verified concerns change. |
| Web/Inertia interfaces and future API governance | [`docs/interfaces/web-inertia.md`](interfaces/web-inertia.md) and [`docs/interfaces/api-governance.md`](interfaces/api-governance.md) | Current web reference; future API decision gate | Interface owner; update when routes, page props, authentication, or approved API contracts change. |
| Development, validation, and release workflow | [`docs/development/workflow.md`](development/workflow.md) | Current | Development owner; update when setup, quality gates, branching, versioning, or releases change. |
| Dependency updates and audits | [`docs/development/dependencies.md`](development/dependencies.md) | Current | Dependency owner; update when ecosystems, schedules, grouping, audit handling, or rollback policy changes. |
| Error monitoring and operational response | [`docs/operations/error-monitoring.md`](operations/error-monitoring.md) | Current | Operations owner; update when monitoring, access, retention, incident response, or rollback changes. |
| Activity logging policy and operations | [`docs/operations/activity-logging.md`](operations/activity-logging.md) | Current | Operations owner; update when the event catalog, metadata policy, access, retention, incident response, or rollback changes. |
| Super-admin provisioning and recovery | [`docs/operations/super-admin-lifecycle.md`](operations/super-admin-lifecycle.md) | Current | Authorization/system owner; update when provisioning, rotation, protected-role invariants, session revocation, or recovery changes. |
| Planning cache policy and operations | [`docs/operations/planning-cache.md`](operations/planning-cache.md) | Current | Operations owner; update when the key format, lifetime, cacheable reads, invalidation, store support, inspection, or rollback changes. |
| Architecture decisions | [`docs/decisions/`](decisions/) | Current through ADR 0002 | Decision owner; add an ADR when an approved choice changes architecture, data, interfaces, or operations. |
| Repository-wide AI-agent instructions | [`AGENTS.md`](../AGENTS.md) | Current | Maintainers; update when repository rules, safety boundaries, or required checks change. |

Planned paths are declarations, not links to implemented documents. Follow the linked issue for the approved scope and delivery status.

## Information types

Use the smallest document that serves the reader:

- **Tutorials** teach a newcomer through a complete learning path.
- **How-to guides** give steps for a specific operational or development task.
- **Reference** records exact interfaces, commands, configuration, and structure.
- **Explanation** documents domain meaning, architecture, and trade-offs.

Keep each document focused on one information type where practical. Link related documents instead of copying their content.

## Source-of-truth rules

1. **Current behavior:** Verify claims against application source, configuration, routes, tests, or a reproducible command. Cite the relevant path or command in technical documents.
2. **Planned behavior:** Link the GitHub issue. Never describe an unmerged feature, API, role, or operational capability as implemented.
3. **Release state:** Use the repository changelog, verified tag, and release PR together. A version label alone does not prove delivery.
4. **Decisions:** Mark an unresolved intent choice `[ASK USER]`. Mark known incomplete documentation or implementation `[TODO]` and link its issue when available.
5. **Disagreement:** Source and verified commands override stale prose. Correct the document or issue in the same change that discovers the discrepancy.

## Update policy

Update documentation in the same issue and pull request when a change affects:

- user-visible behavior or setup instructions;
- domain terms, calculations, precision, or lifecycle rules;
- architecture, module boundaries, dependencies, or integrations;
- routes, Inertia props, authentication, authorization, or an approved API contract;
- environment variables, monitoring, migrations, deployment, or rollback;
- required validation, branch selection, versioning, or release metadata.

The root README remains a concise entry point. Detailed project truth belongs in the canonical location listed above. Generated output, vendor documentation, issue comments, and AI-product adapters must not become competing sources.

## Writing and review checklist

- State the target reader and the task or question the document answers.
- Separate current behavior from planned behavior.
- Use concrete paths and verified commands for technical claims.
- Include prerequisites, risks, and rollback where an operation changes data or infrastructure.
- Keep credentials, personal data, production identifiers, and raw diagnostic details out of documentation.
- Check relative links from the file that contains them.
- Update this map when adding, moving, or retiring a canonical document.
