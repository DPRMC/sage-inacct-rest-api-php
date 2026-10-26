<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Auth\InMemoryTokenStore;
use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Auth\SageOAuthConfig;
use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use Dprmc\SageIntacctRestApi\Exception\SageIntacctException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

final class SageObjectClientTest extends TestCase
{
    public function test_it_lists_an_object_and_applies_entity_scope(): void
    {
        $handler = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'ia::result' => [['id' => 'V-1']],
            ], JSON_THROW_ON_ERROR)),
        ]);
        $httpClient = new Client(['handler' => HandlerStack::create($handler)]);
        $client = $this->client($httpClient)->withEntity('US-ENTITY');

        $response = $client->object('accounts-payable/vendor')->list([
            'fields' => 'id',
        ]);

        self::assertSame([['id' => 'V-1']], $response->result());

        $request = $handler->getLastRequest();
        self::assertSame('/ia/api/v1/objects/accounts-payable/vendor', $request->getUri()->getPath());
        self::assertSame('fields=id', $request->getUri()->getQuery());
        self::assertSame('US-ENTITY', $request->getHeaderLine('X-IA-API-Param-Entity'));
    }

    public function test_it_iterates_all_query_pages(): void
    {
        $handler = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'ia::result' => [['id' => 'V-1'], ['id' => 'V-2']],
                'ia::meta' => ['totalCount' => 3],
            ], JSON_THROW_ON_ERROR)),
            new Response(200, ['Content-Type' => 'application/json'], json_encode([
                'ia::result' => [['id' => 'V-3']],
                'ia::meta' => ['totalCount' => 3],
            ], JSON_THROW_ON_ERROR)),
        ]);
        $client = $this->client(new Client(['handler' => HandlerStack::create($handler)]));

        $records = iterator_to_array(
            $client->object('accounts-payable/vendor')->queryAll(fields: ['id'], pageSize: 2),
            false,
        );

        self::assertSame([
            ['id' => 'V-1'],
            ['id' => 'V-2'],
            ['id' => 'V-3'],
        ], $records);
        self::assertJsonStringEqualsJsonString(
            json_encode([
                'object' => 'accounts-payable/vendor',
                'fields' => ['id'],
                'filters' => [],
                'filterExpression' => 'and',
                'orderBy' => [],
                'start' => 3,
                'size' => 2,
            ], JSON_THROW_ON_ERROR),
            $handler->getLastRequest()->getBody()->getContents(),
        );
    }

    public function test_object_writes_are_disabled_by_default(): void
    {
        $this->expectException(SageIntacctException::class);

        $this->client(new Client(['handler' => HandlerStack::create(new MockHandler())]))
            ->object('accounts-payable/vendor')
            ->create(['name' => 'Not sent']);
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
