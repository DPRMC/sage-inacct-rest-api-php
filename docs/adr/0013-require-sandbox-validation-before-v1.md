# Require sandbox validation before v1

Status: accepted. A dedicated non-production Sage Intacct environment will be used for authenticated integration testing and is a hard prerequisite for `1.0.0`. Mocked and contract tests may establish deterministic correctness, but the production release must also verify each endpoint family against Sage, including mutations, reports, files, asynchronous operations, permissions, rate limits, delivery, and webhooks.
