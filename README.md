# dprmc/sage-inacct-rest-api-php

Framework-agnostic PHP client for the Sage Intacct REST API, with an optional Laravel integration.

The core client does not require Laravel. Laravel users may additionally use the Eloquent models, repositories, writers, migrations, and raw MySQL scripts under `src/Laravel` and `database/`.

## Status

The package currently provides the foundation for:

- OAuth 2.0 token acquisition and refresh.
- Authenticated JSON REST requests.
- Sage response and error normalization.
- Query and object-export helpers.
- Optional Laravel persistence for OAuth tokens and report imports.

The Sage API exposes versioned REST endpoints under `https://api.intacct.com/ia/api/v1`. Confirm the required Sage subscription, application authorization, user permissions, entities, and report access before using the client against production.

## Installation

```bash
composer require dprmc/sage-inacct-rest-api-php
```

## Core client example

```php
use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Auth\SageOAuthConfig;
use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;

$oauthConfig = new SageOAuthConfig(
    clientId: $_ENV['SAGE_CLIENT_ID'],
    clientSecret: $_ENV['SAGE_CLIENT_SECRET'],
    username: $_ENV['SAGE_USERNAME'],
);

$tokenProvider = new OAuthTokenProvider($oauthConfig);
$client = new SageIntacctClient($tokenProvider);

$response = $client->query(
    object: 'accounts-payable/vendor',
    fields: ['id', 'name', 'status'],
    filters: [
        ['$eq' => ['status' => 'active']],
    ],
);

$vendors = $response->result();
```

The client accepts an optional token store, PSR-3 logger, HTTP client, and retry configuration so applications can choose their own persistence and observability mechanisms.

## Laravel integration

Install the Laravel components in the host application if they are not already present:

```bash
composer require illuminate/database illuminate/support
```

Register `Dprmc\SageIntacctRestApi\Laravel\SageIntacctServiceProvider` in the application when automatic discovery is not available. The integration contains:

- Eloquent models for OAuth tokens, report runs, and report records.
- Repositories for reading those records.
- Writers for explicit create/update operations.
- Laravel migrations under `database/migrations`.
- MySQL 8 SQL scripts under `database/sql/mysql8` for environments where migrations cannot be run.

The migrations and SQL scripts create only package-owned tables. They do not modify Sage or existing FIMS tables.

## Security

- Never commit client secrets, access tokens, refresh tokens, passwords, or exported accounting data.
- Use a dedicated Sage integration user with the minimum required permissions.
- Store refresh tokens using the host application's encrypted secret storage.
- Do not log authorization headers or raw token responses.
- Keep write operations disabled until the Sage tenant's mappings and approval workflow are verified.

## Documentation

- [Sage Intacct REST API overview](https://developer.sage.com/intacct)
- [Sage Intacct REST API quick start](https://developer.sage.com/intacct/docs/1/sage-intacct-rest-api/get-started/quick-start)
- [Sage Intacct reports API](https://developer.sage.com/intacct/apis/intacct/1/intacct-openapi/groups/reports?tab=FR)
- [Sage Intacct export API](https://developer.sage.com/intacct/apis/intacct/1/intacct-openapi/groups/common-resources/tags/common_resources_exports/paths/post-core-export)
