# Commit generated SDK artifacts

Status: accepted. Generated endpoint clients, data contracts, and endpoint metadata will be committed under a clearly separated generated namespace alongside the generator and API source snapshot. CI will regenerate them and fail when the generated output differs, so package consumers receive self-contained releases and reviewers can inspect the resulting API surface.

## Considered Options

- Generate only during installation or build: rejected because it makes consumer installs depend on generator tooling and live or external inputs.
- Commit generated output without verification: rejected because generated code could silently drift from the pinned source.
