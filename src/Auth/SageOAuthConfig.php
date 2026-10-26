<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Auth;

final class SageOAuthConfig
{
    public function __construct(
        public readonly string $clientId,
        public readonly string $clientSecret,
        public readonly ?string $username = null,
        public readonly string $tokenEndpoint = 'https://api.intacct.com/ia/api/v1/oauth2/token',
        public readonly string $grantType = 'client_credentials',
        public readonly ?string $scope = null,
        public readonly ?string $redirectUri = null,
    ) {
        if ($this->clientId === '' || $this->clientSecret === '') {
            throw new \InvalidArgumentException('Sage OAuth client credentials are required.');
        }
    }
}
