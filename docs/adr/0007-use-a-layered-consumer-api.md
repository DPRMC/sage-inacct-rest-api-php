# Use a layered consumer API

Status: accepted. The complete SDK will expose typed domain facades as the primary developer experience, generated endpoint clients for exhaustive coverage, and a low-level request API for unusual parameters and forward compatibility. Generic object access remains useful but does not substitute for typed service, report, workflow, file, and asynchronous-operation APIs.

## Consequences

The project must maintain both generated endpoint output and handwritten ergonomic facades. Facades may enforce safety policies such as idempotency keys, write capabilities, response streaming, and asynchronous polling while delegating endpoint shape to generated clients.
