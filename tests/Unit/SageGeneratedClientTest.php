<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Auth\InMemoryTokenStore;
use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Auth\SageOAuthConfig;
use Dprmc\SageIntacctRestApi\Generated\V2026R3\SageIntacctApiClient;
use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use Dprmc\SageIntacctRestApi\Response\SageFileResponse;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class SageGeneratedClientTest extends TestCase
{
    public function test_generated_operation_dispatches_to_the_documented_path(): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'ia::result' => ['id' => 'V-1'],
            ], JSON_THROW_ON_ERROR)),
        ]));
        $handler->push(Middleware::history($history));

        $client = new SageGeneratedClientTestClientFactory()->create($handler);
        $generatedClient = $client->generated();

        $response = $generatedClient->getAccountsPayableVendorKey(
            pathParameters: ['key' => 'V-1'],
            query: ['fields' => 'id,name'],
        );

        self::assertSame(['id' => 'V-1'], $response->result());
        self::assertSame('/ia/api/v1/objects/accounts-payable/vendor/V-1', $history[0]['request']->getUri()->getPath());
        self::assertSame('fields=id%2Cname', $history[0]['request']->getUri()->getQuery());
    }

    public function test_generated_client_exposes_every_catalog_operation(): void
    {
        $operations = SageIntacctApiClient::operations();
        $operationIds = array_column($operations, 'operation_id');

        self::assertCount(2217, $operations);
        self::assertContains('get-accounts-payable-vendor-key', $operationIds);
        self::assertContains('post-services-query', $operationIds);

        foreach ($operationIds as $operationId) {
            $methodName = SageIntacctApiClient::methodForOperationId($operationId);

            self::assertTrue(
                method_exists(SageIntacctApiClient::class, $methodName),
                sprintf('Generated method is missing for operation %s.', $operationId),
            );
        }
    }

    public function test_generated_file_operation_selects_the_documented_media_type(): void
    {
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response(200, ['Content-Type' => 'application/pdf'], '%PDF-1.7 report'),
        ]));
        $handler->push(Middleware::history($history));

        $client = new SageGeneratedClientTestClientFactory()->create($handler);
        $response = $client->generated()->reportDownload();

        self::assertInstanceOf(SageFileResponse::class, $response);
        self::assertSame('application/pdf', $history[0]['request']->getHeaderLine('Accept'));
    }
}

final class SageGeneratedClientTestClientFactory
{
    public function create(HandlerStack $handler): SageIntacctClient
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
