<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Laravel\Writers;

use Dprmc\SageIntacctRestApi\Auth\OAuthToken;
use Dprmc\SageIntacctRestApi\Laravel\Models\SageOAuthToken;

final class SageOAuthTokenWriter
{
    public function write(string $connectionName, OAuthToken $token): SageOAuthToken
    {
        $record = SageOAuthToken::query()
            ->where('connection_name', $connectionName)
            ->first();

        if ($record === null) {
            $record = new SageOAuthToken();
            $record->connection_name = $connectionName;
        }

        $record->access_token = $token->accessToken;
        $record->refresh_token = $token->refreshToken;
        $record->expires_at = $token->expiresAt;
        $record->scope = $token->scope;
        $record->save();

        return $record;
    }
}
