<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Auth\InMemoryTokenStore;
use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Auth\SageOAuthConfig;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;

final class OAuthAuthorizationUrlTest extends TestCase
{
    public function test_it_builds_an_authorization_code_url(): void
    {
        $provider = new OAuthTokenProvider(
            new SageOAuthConfig(
                clientId: 'client-id',
                clientSecret: 'client-secret',
                scope: 'read offline_access',
                redirectUri: 'https://example.test/callback',
            ),
            new InMemoryTokenStore(),
            new Client(['handler' => HandlerStack::create(new MockHandler())]),
        );

        self::assertSame(
            'https://api.intacct.com/ia/api/v1/oauth2/authorize?response_type=code&client_id=client-id&redirect_uri=https%3A%2F%2Fexample.test%2Fcallback&state=state-123&scope=read%20offline_access',
            $provider->authorizationUrl('state-123'),
        );
    }
}
