# Use forward-compatible typed models

Status: accepted. Generated request and response contracts will be typed and immutable, with local validation for documented requirements and enum values. Response models will preserve unknown fields and expose the raw payload so additive Sage changes do not immediately break consumers. Dynamic Sage objects will use a typed record wrapper for known metadata plus tenant-specific fields.

## Consequences

The SDK needs distinct models for JSON resources, collections, asynchronous operations, and binary/file responses. Generated models must not discard fields that are absent from the pinned schema.
