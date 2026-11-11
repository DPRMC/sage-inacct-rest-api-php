# Preserve the 2026 R3 API source snapshot

Status: accepted. The repository will retain an immutable machine-readable snapshot of Sage's published `2026 R3` API definition(s), together with source URLs, retrieval metadata, and a checksum. Generation and review will use this snapshot rather than fetching live documentation during builds; future Sage releases require an explicit source upgrade.

## Considered Options

- Fetch live definitions during every build: rejected because builds would not be reproducible.
- Keep only generated PHP: rejected because the generated surface could not be audited or regenerated reliably.
- Maintain the endpoint inventory manually: rejected because it would drift from Sage's published contract.
