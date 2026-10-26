<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Contracts;

use Dprmc\SageIntacctRestApi\Auth\OAuthToken;

interface TokenStore
{
    public function get(): ?OAuthToken;

    public function put(OAuthToken $token): void;

    public function forget(): void;
}
