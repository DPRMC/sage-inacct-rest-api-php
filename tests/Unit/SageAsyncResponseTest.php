<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Auth\InMemoryTokenStore;
use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Auth\SageOAuthConfig;
use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use Dprmc\SageIntacctRestApi\Response\SageAsyncResponse;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class SageAsyncResponseTest extends TestCase
{
    public function test_accepted_responses_expose_async_metadata(): void
    {
        $client = $this->client(new Client([
            'handler' => HandlerStack::create(new MockHandler([
                new Response(202, [
                    'Content-Type' => 'application/json',
                    'Location' => '/services/reports/status/report-1',
                ], json_encode(['ia::result' => ['id' => 'report-1']], JSON_THROW_ON_ERROR)),
            ])),
        ]));

        $response = $client->post('/services/reports/cash-management/check-register', []);

        self::assertInstanceOf(SageAsyncResponse::class, $response);
        self::assertTrue($response->isAccepted());
        self::assertSame('/services/reports/status/report-1', $response->location);
        self::assertSame(['id' => 'report-1'], $response->result());
    }

    private function client(Client $httpClient): SageIntacctClient
    {
        $tokenProvider = new OAuthTokenProvider(
            new SageOAuthConfig('client-id', 'client-secret'),
            new InMemoryTokenStore(),
            new Client(['handler' => HandlerStack::create(new MockHandler([
                new Response(200, ['Content-Type' => 'application/json'], json_encode([
                    'access_token' => 'access-token',
                    'expires_in' => 3600,
                ], JSON_THROW_ON_ERROR)),
            ]))]),
        );

        return new SageIntacctClient(
            tokenProvider: $tokenProvider,
            httpClient: $httpClient,
            maxRetries: 0,
        );
    }
}
