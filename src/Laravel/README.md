# Laravel integration

The Laravel integration is optional. It is not required to use the core Sage Intacct REST client.

Register the service provider when package discovery is not enabled:

```php
Dprmc\SageIntacctRestApi\Laravel\SageIntacctServiceProvider::class,
```

The integration supplies:

- `SageOAuthToken`, `SageReportRun`, and `SageReportRecord` Eloquent models.
- `EloquentTokenStore` for persisting OAuth tokens through the core `TokenStore` contract.
- Repositories for read operations.
- Writers for explicit token and report-run/record writes.
- Migrations and MySQL 8 SQL scripts in the package root.

The OAuth token model uses Laravel's encrypted casts for access and refresh tokens. The application must still protect its `APP_KEY` and database backups.

The report record table intentionally stores both the source payload and an optional normalized payload. Application-specific FIMS mappings should be implemented by the consuming Laravel application or a separate integration layer rather than hidden inside the generic package.
