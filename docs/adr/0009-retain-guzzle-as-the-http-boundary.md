# Retain Guzzle as the HTTP boundary

Status: accepted. Guzzle remains the SDK's public HTTP dependency and injection boundary. This favors a smaller implementation and continuity with the current library over a PSR-18 abstraction; transport behavior such as authentication, retries, error normalization, streaming, and decoding remains centralized in the handwritten runtime.

## Considered Options

- PSR-18 as the public boundary: rejected for now because it adds adapter and factory complexity without a current consumer requirement.
