# Expenses App documentation

This page maps each kind of project knowledge to one canonical source. Repository documentation describes approved, current behavior. GitHub Issues describe planned work until its implementation and documentation merge.

## Documentation map

| Topic | Canonical source | Status | Owner and update trigger |
| --- | --- | --- | --- |
| Product overview and quick start | [`README.md`](../README.md) | Current | Maintainers; update when supported setup or user-visible capabilities change. |
| Documentation map and policy | [`docs/README.md`](README.md) | Current | Maintainers; update when a documentation category, owner, or canonical location changes. |
| Release history | [`CHANGELOG.md`](../CHANGELOG.md) | Current from 2.0.6 | Release owner; update on every version aggregate. |
| PostgreSQL 18 local upgrade | [`docs/postgresql-18-upgrade.md`](postgresql-18-upgrade.md) | Current | Platform owner; update when the supported PostgreSQL image or migration process changes. |
| Domain language and financial rules | `docs/domain/` | Planned in [#57](https://github.com/lurzar/expenses-app/issues/57) | Domain owner; update when financial meaning, lifecycle, or invariants change. |
| Stack, structure, architecture, conventions, integrations, testing, and concerns | `docs/codebase/` | Planned in [#66](https://github.com/lurzar/expenses-app/issues/66) | Technical owner; update when architecture or supported tooling changes. |
| Web/Inertia interfaces and future API governance | `docs/interfaces/` | Planned in [#58](https://github.com/lurzar/expenses-app/issues/58) | Interface owner; update when routes, page props, authentication, or approved API contracts change. |
| Development, validation, and release workflow | [`docs/development/workflow.md`](development/workflow.md) | Current | Development owner; update when setup, quality gates, branching, versioning, or releases change. |
| Error monitoring and operational response | [`docs/operations/error-monitoring.md`](operations/error-monitoring.md) | Current | Operations owner; update when monitoring, access, retention, incident response, or rollback changes. |
| Activity logging policy and operations | [`docs/operations/activity-logging.md`](operations/activity-logging.md) | Current | Operations owner; update when the event catalog, metadata policy, access, retention, incident response, or rollback changes. |
| Architecture decisions | `docs/decisions/` | Reserved | Decision owner; add an ADR when an approved choice changes architecture, data, interfaces, or operations. |
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
