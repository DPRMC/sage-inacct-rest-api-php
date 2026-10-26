<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Laravel;

use DateTimeImmutable;
use Dprmc\SageIntacctRestApi\Auth\OAuthToken;
use Dprmc\SageIntacctRestApi\Contracts\TokenStore;
use Dprmc\SageIntacctRestApi\Laravel\Models\SageOAuthToken;

final class EloquentTokenStore implements TokenStore
{
    public function __construct(
        private readonly string $connectionName = 'default',
    ) {
    }

    public function get(): ?OAuthToken
    {
        $record = SageOAuthToken::query()
            ->where('connection_name', $this->connectionName)
            ->first();

        if ($record === null || $record->access_token === null || $record->expires_at === null) {
            return null;
        }

        return new OAuthToken(
            accessToken: (string)$record->access_token,
            refreshToken: $record->refresh_token !== null ? (string)$record->refresh_token : null,
            expiresAt: new DateTimeImmutable($record->expires_at->toISOString()),
            scope: $record->scope !== null ? (string)$record->scope : null,
        );
    }

    public function put(OAuthToken $token): void
    {
        $record = SageOAuthToken::query()
            ->where('connection_name', $this->connectionName)
            ->first();

        if ($record === null) {
            $record = new SageOAuthToken();
            $record->connection_name = $this->connectionName;
        }

        $record->access_token = $token->accessToken;
        $record->refresh_token = $token->refreshToken;
        $record->expires_at = $token->expiresAt;
        $record->scope = $token->scope;
        $record->save();
    }

    public function forget(): void
    {
        SageOAuthToken::query()
            ->where('connection_name', $this->connectionName)
            ->delete();
    }
}
