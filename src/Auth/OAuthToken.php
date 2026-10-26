<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Auth;

use DateTimeImmutable;

final class OAuthToken
{
    public function __construct(
        public readonly string $accessToken,
        public readonly ?string $refreshToken,
        public readonly DateTimeImmutable $expiresAt,
        public readonly ?string $scope = null,
        public readonly string $tokenType = 'Bearer',
    ) {
    }

    public function isExpired(int $leewaySeconds = 60): bool
    {
        return $this->expiresAt->getTimestamp() <= time() + $leewaySeconds;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromResponse(array $payload): self
    {
        $accessToken = $payload['access_token'] ?? null;
        $expiresIn = $payload['expires_in'] ?? null;

        if (!is_string($accessToken) || !is_int($expiresIn) && !is_float($expiresIn) && !is_string($expiresIn)) {
            throw new \InvalidArgumentException('Sage OAuth response is missing access_token or expires_in.');
        }

        return new self(
            accessToken: $accessToken,
            refreshToken: isset($payload['refresh_token']) ? (string)$payload['refresh_token'] : null,
            expiresAt: new DateTimeImmutable(sprintf('+%d seconds', (int)$expiresIn)),
            scope: isset($payload['scope']) ? (string)$payload['scope'] : null,
            tokenType: isset($payload['token_type']) ? (string)$payload['token_type'] : 'Bearer',
        );
    }
}
