<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Auth\InMemoryTokenStore;
use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Auth\SageOAuthConfig;
use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use PHPUnit\Framework\TestCase;

final class SageIntacctResourcesTest extends TestCase
{
    public function test_it_exposes_typed_domain_entry_points(): void
    {
        $tokenProvider = new OAuthTokenProvider(
            new SageOAuthConfig('client-id', 'client-secret'),
            new InMemoryTokenStore(),
            new Client(['handler' => HandlerStack::create()]),
        );
        $client = new SageIntacctClient($tokenProvider, httpClient: new Client(), maxRetries: 0);

        self::assertSame('accounts-payable', $client->resources()->accountsPayable()->name());
        self::assertSame('general-ledger', $client->resources()->generalLedger()->name());
        self::assertSame('company-config', $client->resources()->companyConfiguration()->name());
    }
}
