# Complete Sage Intacct REST SDK roadmap

## Target

Release a production-ready `1.0.0` SDK for the Sage Intacct REST API `2026 R3` baseline.

The SDK is complete only when every public endpoint in the pinned Sage definition is inventoried, generated, contract-tested, and verified against the Sage sandbox, with zero unmapped endpoints.

## Non-negotiable decisions

- PHP 8.1+ remains supported.
- Guzzle remains the public HTTP dependency.
- The official `2026 R3` definition is preserved as an immutable source snapshot with metadata and checksum.
- A normalized intermediate representation drives custom PHP generation.
- Generated clients, models, metadata, and contract tests are committed and checked by CI.
- Typed models preserve unknown response fields and expose raw payloads.
- Typed domain facades are the primary API; generated clients and raw requests remain available.
- Reads are safe by default; writes require explicit capabilities.
- Retries are safe by default and never blindly replay mutations.
- Sage definition gaps use reviewed errata, never silent generated-file edits.
- A dedicated Sage sandbox is required before `1.0.0`.

## Phase 0 — Lock the source and release contract

- Obtain the official machine-readable `2026 R3` Sage definitions.
- Store source snapshots, retrieval metadata, URLs, and checksums.
- Define the endpoint completeness report and support-matrix format.
- Define the errata format and review process.
- Confirm sandbox credentials, test companies, entity setup, and safe test-data procedures.
- Add the PHP 8.1+ CI matrix.

Exit criteria: the source is reproducible, the sandbox access path is known, and the completeness report can identify every source operation.

## Phase 1 — Build the generation pipeline

- Add source validation and normalization.
- Create the intermediate representation for endpoints, schemas, media types, authentication, entity scope, mutation safety, retry policy, idempotency, asynchronous behavior, access prerequisites, deprecation, and documentation links.
- Add reviewed errata overlays.
- Create custom templates for endpoint clients, DTOs, response envelopes, file responses, async operations, endpoint metadata, and contract tests.
- Commit generated output under a clearly separated namespace.
- Add a regenerate-and-diff CI check.

Exit criteria: regenerating from the committed snapshot produces no diff, and unsupported or unclassified source behavior fails visibly.

## Phase 2 — Harden the handwritten runtime

- Separate JSON, collection, asynchronous, and binary/file response handling.
- Fix export and report-download handling for non-JSON media types.
- Implement typed error envelopes with status, Sage error code, request context, and retry metadata.
- Implement bounded exponential backoff with jitter and `Retry-After` support.
- Retry safe reads and read-only POST services; require explicit idempotency semantics for replayable mutations.
- Refresh and retry once on `401`.
- Add rate-limit metadata and observability without logging credentials or sensitive payloads.
- Implement explicit write capabilities and destructive-operation safeguards.
- Validate actual Sage webhook header envelopes and implement idempotency support at the integration boundary.
- Preserve entity scoping, pagination, OAuth persistence, and Laravel integration under the new response/runtime model.

Exit criteria: runtime unit tests cover all safety policies, response kinds, authentication paths, and failure modes.

## Phase 3 — Generate and expose the complete endpoint surface

- Generate authentication and token endpoints.
- Generate every object collection, item, CRUD operation, and workflow.
- Generate core query and export services.
- Generate reports, report status, report retrieval, report download, cancellation, and other report services.
- Generate asynchronous job and operation polling APIs.
- Generate all file and non-JSON response endpoints.
- Generate delivery queue endpoints and webhook-related integration helpers.
- Generate permission-gated and subscription-gated endpoints with prerequisite metadata.
- Add handwritten domain facades for common and safety-sensitive workflows.
- Preserve the low-level request escape hatch for forward compatibility.

Exit criteria: every source endpoint has generated code and a discoverable typed entry point; no endpoint is silently omitted.

## Phase 4 — Build the verification system

- Generate at least one deterministic contract test per endpoint.
- Add runtime tests for serialization, validation, errors, retries, pagination, files, async operations, entity scope, and OAuth.
- Add negative tests for authorization, permissions, invalid payloads, expired tokens, rate limits, and malformed responses.
- Run authenticated sandbox tests for every endpoint family, including safe mutation workflows.
- Record sandbox evidence and known permission/subscription prerequisites.
- Produce a completeness report with discovered, normalized, generated, contract-tested, sandbox-verified, deprecated, and errata-covered counts.

Exit criteria: every endpoint is generated, tested, sandbox-verified, and classified; unmapped count is zero.

## Phase 5 — Release `1.0.0`

- Publish generated API documentation and the endpoint support matrix.
- Document authentication, capabilities, retries, idempotency, files, reports, webhooks, sandbox setup, and error handling.
- Add migration guidance from the current pre-1.0 API.
- Run Composer validation, syntax checks, unit tests, generated-contract tests, sandbox tests, and regeneration checks across PHP 8.1+.
- Review security and secret-handling practices.
- Tag `1.0.0` only after the completeness gate passes.

## Future Sage release process

Each Sage release is handled through an explicit upgrade:

1. Add the official source snapshot.
2. Generate a source-to-source endpoint and schema diff.
3. Review additions, changes, deprecations, removals, and errata.
4. Regenerate code, metadata, tests, and documentation.
5. Run deterministic tests and sandbox tests for changed families.
6. Publish compatibility notes and a new SDK release.

Normal builds never fetch or silently adopt a newer Sage baseline.
