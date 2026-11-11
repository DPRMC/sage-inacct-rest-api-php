# Sage Intacct REST SDK

This context defines the language and boundaries for the Sage Intacct integration library.

## Scope

**Complete Sage Intacct REST SDK**:
A client library whose supported contract covers every endpoint published in the selected Sage Intacct REST API release baseline, including authentication, object resources, workflows, service endpoints, reports, files, delivery, and webhooks.
_Avoid_: Generic Sage client, partial endpoint wrapper, transport-only client

**API release baseline**:
The specific Sage Intacct REST API release whose published endpoint and schema set defines a complete SDK release. The initial baseline is `2026 R3`.
_Avoid_: Latest documentation, floating API version

**Schema-driven SDK**:
A complete SDK whose endpoint and data-contract surface is derived from the pinned Sage API definition, while integration behavior and safety policies remain deliberately authored and tested.
_Avoid_: Hand-written endpoint inventory, transport-only wrapper

**Complete surface**:
Every publicly documented REST endpoint in the `2026 R3` definition, including permission-gated, asynchronous, report, file, delivery, and authentication endpoints. Deprecated endpoints are excluded unless Sage includes them in that release definition; webhook receiving is an integration capability rather than a REST endpoint.
_Avoid_: Core endpoints only, object CRUD only

**API source snapshot**:
An immutable, machine-readable copy of Sage's published API definition for the selected release, retained with its source URL, retrieval metadata, and checksum so the SDK surface can be regenerated and audited.
_Avoid_: Live documentation, generated output as the source of truth

**Generated artifact**:
Committed SDK output produced from the API source snapshot, including endpoint clients, data contracts, and endpoint metadata. Generated artifacts are checked by CI against a fresh generation.
_Avoid_: Build-time-only client, hand-edited generated file

**Production compatibility boundary**:
The first production release, targeted as `1.0.0`, after which public API changes require an explicit compatibility policy. Before that release, breaking changes are allowed when they improve correctness or completeness.
_Avoid_: Current prototype API as a permanent contract

**Layered SDK API**:
The consumer-facing SDK has typed domain facades first, generated endpoint clients beneath them, and a low-level request escape hatch for unusual or newly released operations. Generic object access is a convenience, not the definition of completeness.
_Avoid_: Generated-only API, raw HTTP as the primary interface

**Runtime support floor**:
The core SDK supports PHP 8.1 and newer, with compatibility verified across the supported PHP release matrix.
_Avoid_: PHP 8.3-only SDK, untested version claim

**HTTP transport**:
Guzzle is the public HTTP dependency and injection boundary for the SDK. Transport behavior remains behind the handwritten runtime, but consumers may configure or replace the injected Guzzle client.
_Avoid_: PSR-18 as the required public transport contract

**Typed contract**:
A generated request or response model for a documented endpoint that validates known fields while preserving unknown response fields and the original raw payload for forward compatibility.
_Avoid_: Arrays everywhere, reject-on-unknown DTOs

**Safe retry**:
A bounded, observable retry policy that retries safe reads by default and retries mutations only when the operation and caller-provided idempotency strategy make replay safe.
_Avoid_: Retry every request, blind write retry

**Write capability**:
An explicit client-level permission to invoke Sage mutations. The SDK is read-only by default; destructive and workflow mutations require deliberate capability configuration and endpoint-specific safety metadata.
_Avoid_: Writes enabled by default, Sage permissions as the only safeguard

**Sandbox validation**:
Authenticated testing against a dedicated non-production Sage Intacct company used to verify generated requests, real response shapes, permissions, workflows, reports, files, rate limits, and webhook behavior before the production compatibility boundary.
_Avoid_: Mock-only release, production smoke test as validation

**SDK generation pipeline**:
The development process that turns Sage's official API snapshots into a normalized endpoint inventory and then into committed PHP clients, models, metadata, and contract tests through project-owned templates.
_Avoid_: Runtime code generation, generic generator output as the public API

**Endpoint behavior metadata**:
The normalized description of an endpoint's method, path, contracts, media types, authentication, entity scope, mutation safety, retry and idempotency rules, asynchronous behavior, destructive potential, access prerequisites, deprecation, and documentation source.
_Avoid_: Path-only endpoint inventory, method-only coverage

**Completeness gate**:
The current `1.0.0` release rule that every endpoint in the pinned release is inventoried, generated, contract-tested, and sandbox-verified with zero unmapped endpoints. This rule is intentionally revisitable as release evidence and scope evolve.
_Avoid_: Best-effort coverage, endpoint-count-only completeness

**Sage API erratum**:
A reviewed, temporary correction applied outside the immutable Sage source snapshot when published documentation or machine-readable definitions are incomplete or contradictory. Every erratum carries its source, rationale, affected endpoint, expected behavior, and regression evidence.
_Avoid_: Silent generated-file patch, undocumented workaround

**Sage release upgrade**:
An explicit, versioned compatibility change that replaces the pinned API source snapshot, diffs the endpoint surface, regenerates the SDK, reruns tests, validates changed endpoint families in the sandbox, and publishes compatibility notes.
_Avoid_: Automatic documentation refresh, silent baseline drift
