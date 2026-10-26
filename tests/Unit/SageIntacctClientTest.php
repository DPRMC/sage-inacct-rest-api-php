<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Auth\InMemoryTokenStore;
use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Auth\SageOAuthConfig;
use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use Dprmc\SageIntacctRestApi\Exception\SageIntacctRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class SageIntacctClientTest extends TestCase
{
    public function test_it_queries_an_object_and_returns_the_sage_result(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'ia::result' => [
                    ['id' => 'V-1', 'name' => 'Vendor One'],
                ],
                'ia::meta' => ['totalCount' => 1],
            ], JSON_THROW_ON_ERROR)),
        ]));

        $client = $this->client($handler);
        $response = $client->query('accounts-payable/vendor', ['id', 'name']);

        self::assertSame([['id' => 'V-1', 'name' => 'Vendor One']], $response->result());
        self::assertSame(1, $response->meta()['totalCount']);
    }

    public function test_it_throws_with_the_sage_error_payload(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(400, ['Content-Type' => 'application/json'], json_encode([
                'ia::error' => ['code' => 'invalidRequest', 'message' => 'Invalid query'],
            ], JSON_THROW_ON_ERROR)),
        ]));

        $client = $this->client($handler);

        $this->expectException(SageIntacctRequestException::class);
        $this->expectExceptionCode(400);

        $client->get('/objects/accounts-payable/vendor');
    }

    private function client(HandlerStack $handler): SageIntacctClient
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
            httpClient: new Client(['handler' => $handler]),
            maxRetries: 0,
        );
    }
}
