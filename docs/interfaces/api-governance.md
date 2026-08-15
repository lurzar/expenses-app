# Future HTTP API governance

This document is the decision gate for a future machine-readable HTTP API. It does not advertise an API and contains no endpoint examples because none are implemented.

## Current status

- No `routes/api.php`, API controller/resource, `docs/api/openapi.yaml`, or application API test suite exists.
- `laravel/sanctum` and `HasApiTokens` are installed capabilities, not proof of a supported token interface.
- All 28 application routes are session/CSRF-oriented web routes.
- Inertia props are browser page contracts, not stable public JSON resources.

Therefore the current public API surface is **none**. Consumers must not automate against Inertia payloads or undocumented web responses.

## Contract-first source of truth

The first approved API feature must create `docs/api/openapi.yaml`. From that merge onward:

1. OpenAPI is canonical for paths, methods, parameters, schemas, authentication, responses, examples, and deprecations.
2. Laravel routes, requests, resources, policies, and tests implement the committed contract.
3. Generated HTML/interactive documentation is build output derived from OpenAPI, never a competing hand-edited source.
4. The approved exact-head quality gate validates the OpenAPI document and tests implemented request/response examples before merge.
5. Unimplemented paths are not added merely to describe a roadmap.

## First-API approval checklist

An implementation issue must name and approve all of the following before code starts:

- first consumer and concrete use case;
- data owner and records/resources required;
- read/write operations and idempotency needs;
- threat model and acceptable token lifetime/revocation process;
- authorization abilities and owner/administrator boundary;
- expected request rate, result size, and pagination behavior;
- compatibility/deprecation owner and supported client window;
- sensitive-field classification, logging/redaction, and audit requirements;
- rollout, monitoring, incident response, and rollback.

Without a named consumer, do not create generic endpoints or broad tokens.

## Versioning and compatibility policy

- Put the major version in the URI: `/api/v1/...`.
- Additive optional fields and new endpoints may ship within the same major version.
- Removing/renaming a field, changing its meaning/type/nullability, tightening a previously valid request without a transition, or changing authentication/error semantics is breaking and requires a new major version.
- Deprecation must be represented in OpenAPI, announced in release notes, and retain the previous major version for the approved support window.
- Clients must ignore unknown response fields; servers must reject unknown writable fields unless the schema explicitly permits them.
- Public identifiers are ULID strings. Internal numeric database IDs never enter the contract.

## Authentication and authorization model

The default model is Laravel Sanctum bearer tokens tied to a real user and limited by named abilities. The first API issue must narrow the ability catalog to its use case; no wildcard production token is part of the default design.

- Authenticate over HTTPS only.
- Store only token hashes server-side; show the plaintext token once at issuance.
- Require explicit expiry and revocation behavior in the first API issue.
- Apply policies/scoped queries after authentication; a token ability is not record-level authorization.
- Use separate abilities for reads and mutations where both exist.
- Session-cookie/CSRF authentication remains the web-interface model and must not be silently mixed with bearer-token examples.

If the first consumer needs OAuth delegation, service accounts, or first-party SPA cookies instead, that issue must replace this default through an ADR before endpoint work.

## Resource and request rules

- Use plural nouns for collection paths and stable resource schemas rather than serializing Eloquent models directly.
- Laravel API Resources (or an equivalent explicit transformer) own response selection and naming.
- Accept JSON with an explicit media type and UTF-8 encoding; document maximum body/item counts.
- Represent MYR amounts as decimal strings with exactly two fractional digits, derived from server integer-sen calculations. Never expose binary floats as financial truth.
- Represent timestamps in ISO 8601 UTC and calendar plan periods with explicit integer month/year fields.
- Mutations validate unknown fields, financial bounds, ownership, and idempotency requirements before persistence.
- Do not expose activity/Telescope/debug payloads through a product API.

## Response, error, and pagination shape

Single-resource success:

```json
{
  "data": {
    "id": "01J..."
  }
}
```

Collection success uses cursor pagination by default:

```json
{
  "data": [],
  "links": {
    "next": null
  },
  "meta": {
    "per_page": 25
  }
}
```

The contract must define maximum `per_page`, stable ordering, cursor opacity, and filter semantics for each collection.

Errors use one envelope across validation, authentication, authorization, conflicts, rate limits, and server failures:

```json
{
  "error": {
    "code": "validation_failed",
    "message": "The request could not be processed.",
    "fields": {
      "income": ["The income format is invalid."]
    },
    "request_id": "01J..."
  }
}
```

- `code` is a stable machine identifier; `message` is safe for humans.
- `fields` appears only for field-addressable validation failures.
- `request_id` correlates sanitized logs without exposing a stack trace.
- OpenAPI enumerates status codes and error codes per operation.
- Production responses never include exception classes, SQL, file paths, tokens, or debug context.

## Security, limits, and operations

- Apply rate limits by authenticated identity and endpoint sensitivity; document limit headers/429 behavior.
- Enforce policy checks and user scoping in automated owner/non-owner tests.
- Minimize response fields and activity metadata; classify personal/financial data before release.
- Record security-relevant token lifecycle and mutations through an allowlisted audit event contract.
- Define timeout, retry, and idempotency behavior for writes; clients must not blindly retry non-idempotent requests.
- Publish monitoring/alert/rollback ownership before the first production consumer is enabled.

## Required validation

The first API delivery must add deterministic commands for:

- OpenAPI syntax and semantic validation;
- implemented-route versus documented-operation parity;
- request/response schema examples;
- authentication, ability, owner/non-owner, validation, rate-limit, and error-shape tests;
- compatibility/diff detection for later contract changes.

Issue #65 owns the general local quality foundation. A future API issue owns these API-specific gates and must make them mandatory for its paths without assuming a paid hosted runner.

## Evidence and related decisions

- `php artisan route:list --except-vendor --json`
- `app/Models/User.php` (`HasApiTokens` capability)
- `composer.lock` (`laravel/sanctum`)
- `app/Http/Middleware/HandleInertiaRequests.php`
- `resources/js/app.tsx`
- [`web-inertia.md`](web-inertia.md)
- [`../domain/expense-planning.md`](../domain/expense-planning.md)
- #58 owns this governance baseline; a consumer-specific issue owns any future API implementation.
