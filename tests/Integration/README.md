# Read-only Sage Intacct integration test

The integration suite is opt-in and performs one read-only query against Sage Intacct. It does not create, update, delete, approve, post, or attach any Sage records.

The test uses the Sage REST OAuth client-credentials flow and queries one record from the configured object. It defaults to `accounts-payable/vendor` and requests only the `id` field.

Set the credentials in the shell or in a local untracked `.env.integration` file loaded by your shell. Do not place credentials in PHPUnit XML, source files, fixtures, CI configuration, or GitHub Actions logs.

```bash
export SAGE_INTEGRATION_CLIENT_ID='...'
export SAGE_INTEGRATION_CLIENT_SECRET='...'
export SAGE_INTEGRATION_USERNAME='user@company|Top level'
export SAGE_INTEGRATION_READ_ONLY=1
export SAGE_INTEGRATION_REQUIRE_CREDENTIALS=1

composer test:integration
```

Optional variables:

```bash
export SAGE_INTEGRATION_BASE_URL='https://api.intacct.com/ia/api/v1'
export SAGE_INTEGRATION_OBJECT='accounts-payable/vendor'
export SAGE_INTEGRATION_SCOPE='offline_access'
```

The integration test is skipped when configuration is absent. Setting `SAGE_INTEGRATION_REQUIRE_CREDENTIALS=1` converts missing configuration into a test failure, which is appropriate for a deliberate local production verification.
