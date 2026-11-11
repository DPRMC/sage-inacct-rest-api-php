# Use a hybrid schema-driven SDK

Status: accepted. The SDK will generate endpoint inventory, request/response contracts, object operations, service methods, and endpoint contract tests from the pinned Sage Intacct `2026 R3` definition. Authentication, transport, retry policy, error normalization, pagination, entity scoping, binary responses, webhook verification, and Laravel integration will remain hand-authored because they encode behavior and safety policy rather than endpoint shape.

## Considered Options

- Fully hand-authored endpoint clients: rejected because omission and release-drift risk are too high for a complete SDK.
- Fully generated client: rejected because Sage-specific behavior and safety policies need deliberate implementation.
- Generic transport only: rejected because it does not provide a complete, discoverable SDK contract.
