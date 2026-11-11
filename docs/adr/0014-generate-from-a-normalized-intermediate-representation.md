# Generate from a normalized intermediate representation

Status: accepted. A development-only pipeline will validate Sage's official API snapshots, normalize them into a project-owned intermediate representation, and render committed PHP clients, models, endpoint metadata, and contract tests through custom templates. Generic OpenAPI tooling may parse and validate source documents, but its generated client will not define the public API.

## Consequences

The intermediate representation becomes the reviewable bridge between Sage documentation and generated code. It must preserve Sage-specific media types, envelopes, asynchronous behavior, permissions, entity scope, mutation safety, and source links.
