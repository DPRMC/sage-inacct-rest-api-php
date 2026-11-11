# Carry safety and behavior metadata through generation

Status: accepted. The normalized endpoint representation will retain not only paths and schemas but also response media types, authentication, entity scope, mutation and destructive-operation classification, retry and idempotency rules, asynchronous behavior, subscription or permission prerequisites, deprecation, and Sage documentation links. Generated clients, facades, tests, and documentation will consume this metadata.

## Consequences

Completeness can be measured at the endpoint-contract level while runtime safety remains explicit. The generator must fail or require an explicit review decision when the source contains behavior it cannot classify.
