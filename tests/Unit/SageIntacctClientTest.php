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
use GuzzleHttp\Middleware;
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

        try {
            $client->get('/objects/accounts-payable/vendor');
            self::fail('Expected Sage request exception.');
        } catch (SageIntacctRequestException $exception) {
            self::assertSame(400, $exception->httpStatus);
            self::assertSame('invalidRequest', $exception->errorCode);
            self::assertSame(['ia::error' => ['code' => 'invalidRequest', 'message' => 'Invalid query']], $exception->errorPayload);
        }
    }

    public function test_it_exposes_request_and_retry_metadata_on_errors(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(429, [
                'Content-Type' => 'application/json',
                'X-Request-ID' => 'request-123',
                'Retry-After' => '120',
            ], json_encode([
                'error' => ['code' => 'rateLimited'],
            ], JSON_THROW_ON_ERROR)),
        ]));

        $client = $this->client($handler);

        try {
            $client->get('/objects/accounts-payable/vendor');
            self::fail('Expected Sage request exception.');
        } catch (SageIntacctRequestException $exception) {
            self::assertSame('rateLimited', $exception->errorCode);
            self::assertSame('request-123', $exception->requestId);
            self::assertSame(60.0, $exception->retryAfterSeconds);
        }
    }

    public function test_it_retries_safe_reads_but_not_writes_without_idempotency_key(): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(503, ['Content-Type' => 'application/json']),
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'ia::result' => ['id' => 'V-1'],
            ], JSON_THROW_ON_ERROR)),
            new Response(503, ['Content-Type' => 'application/json']),
        ]));
        $handler->push(Middleware::history($history));

        $client = $this->client($handler, maxRetries: 1);

        $readResponse = $client->get('/objects/accounts-payable/vendor/V-1');

        self::assertSame(['id' => 'V-1'], $readResponse->result());

        $this->expectException(SageIntacctRequestException::class);
        $this->expectExceptionCode(503);

        $client->post('/objects/accounts-payable/vendor', ['name' => 'Vendor One']);

        self::assertCount(3, $history);
    }

    public function test_it_retries_a_write_when_an_idempotency_key_is_present(): void
    {
        $handler = HandlerStack::create(new MockHandler([
            new Response(503, ['Content-Type' => 'application/json']),
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'ia::result' => ['id' => 'V-1'],
            ], JSON_THROW_ON_ERROR)),
        ]));

        $client = $this->client($handler, maxRetries: 1);
        $response = $client->post(
            '/objects/accounts-payable/vendor',
            ['name' => 'Vendor One'],
            ['Idempotency-Key' => 'vendor-create-1'],
        );

        self::assertSame(['id' => 'V-1'], $response->result());
    }

    private function client(HandlerStack $handler, int $maxRetries = 0): SageIntacctClient
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
            maxRetries: $maxRetries,
            retryDelaysInSeconds: [0],
        );
    }
}
