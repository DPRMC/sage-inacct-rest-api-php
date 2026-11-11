# Keep writes disabled by default

Status: accepted. The SDK will construct read-only clients by default. Consumers must explicitly enable write capabilities, and generated endpoint metadata will identify mutation and destructive operations so handwritten facades can require additional safety controls such as idempotency keys and explicit cancellation or reversal methods.

## Consequences

Read-only integrations remain safe to initialize with broad credentials, while write-enabled integrations require visible configuration and focused tests. The SDK must not treat Sage-side permissions as the only protection against application mistakes.
