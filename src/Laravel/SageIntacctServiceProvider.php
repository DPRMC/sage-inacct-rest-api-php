<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Laravel;

use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Auth\SageOAuthConfig;
use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use Illuminate\Support\ServiceProvider;

final class SageIntacctServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/sage-intacct.php', 'sage-intacct');

        $this->app->singleton(SageOAuthConfig::class, function (): SageOAuthConfig {
            return new SageOAuthConfig(
                clientId: (string)config('sage-intacct.client_id'),
                clientSecret: (string)config('sage-intacct.client_secret'),
                username: config('sage-intacct.username'),
                tokenEndpoint: (string)config('sage-intacct.token_endpoint'),
                scope: config('sage-intacct.scope'),
                authorizationEndpoint: (string)config('sage-intacct.authorization_endpoint'),
            );
        });

        $this->app->singleton(OAuthTokenProvider::class, function ($app): OAuthTokenProvider {
            $tokenStore = new EloquentTokenStore((string)config('sage-intacct.connection_name'));

            return new OAuthTokenProvider(
                config: $app->make(SageOAuthConfig::class),
                tokenStore: $tokenStore,
            );
        });

        $this->app->singleton(SageIntacctClient::class, function ($app): SageIntacctClient {
            return new SageIntacctClient(
                tokenProvider: $app->make(OAuthTokenProvider::class),
                baseUrl: (string)config('sage-intacct.base_url'),
                allowWrites: (bool)config('sage-intacct.allow_writes'),
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../database/migrations' => database_path('migrations'),
            ], 'sage-intacct-migrations');

            $this->publishes([
                __DIR__ . '/../../config/sage-intacct.php' => config_path('sage-intacct.php'),
            ], 'sage-intacct-config');
        }
    }
}
