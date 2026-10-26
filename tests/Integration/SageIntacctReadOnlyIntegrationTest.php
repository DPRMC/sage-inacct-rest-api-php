<?php

declare(strict_types=1);

namespace Tests\Integration;

use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Auth\SageOAuthConfig;
use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use PHPUnit\Framework\TestCase;

final class SageIntacctReadOnlyIntegrationTest extends TestCase
{
    public function test_it_can_query_one_sage_record_without_writing_data(): void
    {
        $this->requireReadOnlyIntegrationConfiguration();

        $oauthConfig = new SageOAuthConfig(
            clientId: $this->environmentValue('SAGE_INTEGRATION_CLIENT_ID'),
            clientSecret: $this->environmentValue('SAGE_INTEGRATION_CLIENT_SECRET'),
            username: $this->environmentValue('SAGE_INTEGRATION_USERNAME'),
            scope: $this->optionalEnvironmentValue('SAGE_INTEGRATION_SCOPE'),
        );

        $client = new SageIntacctClient(
            tokenProvider: new OAuthTokenProvider($oauthConfig),
            baseUrl: $this->optionalEnvironmentValue('SAGE_INTEGRATION_BASE_URL')
                ?? 'https://api.intacct.com/ia/api/v1',
            maxRetries: 1,
        );

        $response = $client->query(
            object: $this->optionalEnvironmentValue('SAGE_INTEGRATION_OBJECT')
                ?? 'accounts-payable/vendor',
            fields: ['id'],
            filters: [],
            start: 1,
            size: 1,
        );

        self::assertIsArray($response->result());
        self::assertLessThanOrEqual(1, count($response->result()));
    }

    private function requireReadOnlyIntegrationConfiguration(): void
    {
        $requiredVariables = [
            'SAGE_INTEGRATION_CLIENT_ID',
            'SAGE_INTEGRATION_CLIENT_SECRET',
            'SAGE_INTEGRATION_USERNAME',
            'SAGE_INTEGRATION_READ_ONLY',
        ];
        $missingVariables = [];

        foreach ($requiredVariables as $variableName) {
            if (($value = getenv($variableName)) === false || trim($value) === '') {
                $missingVariables[] = $variableName;
            }
        }

        if (getenv('SAGE_INTEGRATION_READ_ONLY') !== '1') {
            $missingVariables[] = 'SAGE_INTEGRATION_READ_ONLY=1';
        }

        if ($missingVariables === []) {
            return;
        }

        $message = 'Sage read-only integration test requires: ' . implode(', ', $missingVariables);

        if (getenv('SAGE_INTEGRATION_REQUIRE_CREDENTIALS') === '1') {
            self::fail($message);
        }

        self::markTestSkipped($message);
    }

    private function environmentValue(string $name): string
    {
        $value = getenv($name);

        if ($value === false || trim($value) === '') {
            self::fail('Missing required environment variable: ' . $name);
        }

        return $value;
    }

    private function optionalEnvironmentValue(string $name): ?string
    {
        $value = getenv($name);

        if ($value === false || trim($value) === '') {
            return null;
        }

        return $value;
    }
}
