<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Auth\InMemoryTokenStore;
use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Auth\SageOAuthConfig;
use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use Dprmc\SageIntacctRestApi\Response\SageFileResponse;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class SageFileResponseTest extends TestCase
{
    public function test_it_returns_non_json_success_responses_as_files(): void
    {
        $handler = new MockHandler([
            new Response(200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="report.pdf"',
            ], '%PDF-1.7 report'),
        ]);
        $client = $this->client(new Client(['handler' => HandlerStack::create($handler)]));

        $response = $client->get('/services/reports/download');

        self::assertInstanceOf(SageFileResponse::class, $response);
        self::assertSame('%PDF-1.7 report', $response->body());
        self::assertSame('application/pdf', $response->contentType());
        self::assertSame('attachment; filename="report.pdf"', $response->header('Content-Disposition'));
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
