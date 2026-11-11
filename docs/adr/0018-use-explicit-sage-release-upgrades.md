# Use explicit Sage release upgrades

Status: accepted. Each future Sage API baseline will be adopted through an explicit versioned release process: add the official snapshot, diff the endpoint surface, review changes and errata, regenerate code and tests, run deterministic and sandbox validation, and publish compatibility notes. Normal builds will never fetch or silently adopt a newer Sage release.

## Consequences

The package can make reproducible compatibility claims, while Sage upgrades become visible engineering work with a reviewable impact report.
