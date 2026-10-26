<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Auth;

use Dprmc\SageIntacctRestApi\Contracts\TokenStore;

final class InMemoryTokenStore implements TokenStore
{
    private ?OAuthToken $token = null;

    public function get(): ?OAuthToken
    {
        return $this->token;
    }

    public function put(OAuthToken $token): void
    {
        $this->token = $token;
    }

    public function forget(): void
    {
        $this->token = null;
    }
}
