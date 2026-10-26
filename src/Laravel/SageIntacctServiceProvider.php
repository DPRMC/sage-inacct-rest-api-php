<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Laravel;

use Illuminate\Support\ServiceProvider;

final class SageIntacctServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../database/migrations' => database_path('migrations'),
            ], 'sage-intacct-migrations');
        }
    }
}
