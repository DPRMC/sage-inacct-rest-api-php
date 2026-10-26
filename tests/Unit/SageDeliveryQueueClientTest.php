<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Auth\InMemoryTokenStore;
use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Auth\SageOAuthConfig;
use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use Dprmc\SageIntacctRestApi\Services\SageDeliveryQueueClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class SageDeliveryQueueClientTest extends TestCase
{
    public function test_it_reads_and_acknowledges_delivery_events(): void
    {
        $handler = new MockHandler([
            $this->jsonResponse(['ia::result' => ['count' => 0]]),
            $this->jsonResponse(['ia::result' => []]),
            $this->jsonResponse(['ia::result' => ['acknowledged' => true]]),
        ]);
        $client = $this->client(new Client(['handler' => HandlerStack::create($handler)]));
        $queue = new SageDeliveryQueueClient($client);

        self::assertSame(['count' => 0], $queue->count()->result());
        self::assertSame([], $queue->list()->result());
        self::assertSame(['acknowledged' => true], $queue->acknowledge(['ids' => ['event-1']])->result());
    }

    private function client(Client $httpClient): SageIntacctClient
    {
        $tokenProvider = new OAuthTokenProvider(
            new SageOAuthConfig('client-id', 'client-secret'),
            new InMemoryTokenStore(),
            new Client(['handler' => HandlerStack::create(new MockHandler([
                $this->jsonResponse([
                    'access_token' => 'access-token',
                    'expires_in' => 3600,
                ]),
            ]))]),
        );

        return new SageIntacctClient(
            tokenProvider: $tokenProvider,
            httpClient: $httpClient,
            maxRetries: 0,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function jsonResponse(array $payload): Response
    {
        return new Response(
            200,
            ['Content-Type' => 'application/json'],
            json_encode($payload, JSON_THROW_ON_ERROR),
        );
    }
}
