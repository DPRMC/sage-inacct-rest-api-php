<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Laravel\Repositories;

use Dprmc\SageIntacctRestApi\Laravel\Models\SageOAuthToken;

final class SageOAuthTokenRepository
{
    public function findByConnectionName(string $connectionName): ?SageOAuthToken
    {
        return SageOAuthToken::query()
            ->where('connection_name', $connectionName)
            ->first();
    }
}
