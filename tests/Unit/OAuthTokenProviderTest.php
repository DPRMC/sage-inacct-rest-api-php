<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Auth\InMemoryTokenStore;
use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Auth\SageOAuthConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class OAuthTokenProviderTest extends TestCase
{
    public function test_it_requests_and_reuses_a_client_credentials_token(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'access_token' => 'access-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ], JSON_THROW_ON_ERROR)),
        ]));

        $provider = new OAuthTokenProvider(
            new SageOAuthConfig('client-id', 'client-secret', 'user@company|Top level'),
            new InMemoryTokenStore(),
            new Client(['handler' => $handler]),
        );

        self::assertSame('access-token', $provider->getAccessToken());
        self::assertSame('access-token', $provider->getAccessToken());
    }

    public function test_it_refreshes_an_expired_authorization_code_token(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'access_token' => 'refreshed-token',
                'refresh_token' => 'rotated-refresh-token',
                'expires_in' => 3600,
            ], JSON_THROW_ON_ERROR)),
        ]));

        $store = new InMemoryTokenStore();
        $store->put(new \Dprmc\SageIntacctRestApi\Auth\OAuthToken(
            accessToken: 'expired-token',
            refreshToken: 'refresh-token',
            expiresAt: new \DateTimeImmutable('-1 minute'),
        ));

        $provider = new OAuthTokenProvider(
            new SageOAuthConfig('client-id', 'client-secret', grantType: 'authorization_code'),
            $store,
            new Client(['handler' => $handler]),
        );

        self::assertSame('refreshed-token', $provider->getAccessToken());
        self::assertSame('rotated-refresh-token', $store->get()?->refreshToken);
    }
}
