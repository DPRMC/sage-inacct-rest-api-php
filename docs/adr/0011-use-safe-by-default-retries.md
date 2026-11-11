# Use safe-by-default retries

Status: accepted. The SDK will automatically retry safe reads and read-only POST services, honor `Retry-After`, use bounded exponential backoff with jitter, refresh once on `401`, and expose rate-limit metadata. Mutations will not be blindly retried; a mutation may be retried only when its endpoint semantics and caller-provided idempotency strategy make replay safe.

## Consequences

Endpoint metadata must identify retry safety, and the generated/runtime layers must distinguish read-only POSTs from creates, deletes, and workflows. Retry decisions and attempts must be observable without logging credentials or payload secrets.
