# Pin the initial SDK to Sage Intacct 2026 R3

Status: accepted. The first complete SDK release will target the Sage Intacct REST API `2026 R3` baseline. Pinning the baseline makes endpoint inventory, generated contracts, tests, and compatibility claims reproducible; later Sage releases will be adopted through explicit compatibility upgrades.

## Considered Options

- Always target the latest documentation: rejected because the support contract would drift continuously.
- Support multiple baselines immediately: rejected because it multiplies schema and integration-test maintenance before the first complete release.
