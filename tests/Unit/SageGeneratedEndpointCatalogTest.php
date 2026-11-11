<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class SageGeneratedEndpointCatalogTest extends TestCase
{
    public function test_the_generated_2026_r3_catalog_has_one_entry_for_every_operation(): void
    {
        $path = dirname(__DIR__, 2) . '/resources/generated/2026-r3/endpoints.json';
        $contents = file_get_contents($path);

        self::assertNotFalse($contents);

        $catalog = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($catalog);
        self::assertCount(2217, $catalog);

        $operationIds = [];

        foreach ($catalog as $endpoint) {
            self::assertIsArray($endpoint);
            self::assertIsString($endpoint['operation_id'] ?? null);
            self::assertIsString($endpoint['method'] ?? null);
            self::assertIsString($endpoint['path'] ?? null);
            self::assertArrayHasKey('request_media_types', $endpoint);
            self::assertArrayHasKey('response_media_types', $endpoint);
            self::assertArrayHasKey('retry_class', $endpoint);
            self::assertArrayHasKey('requires_idempotency_for_retry', $endpoint);

            $operationIds[] = $endpoint['operation_id'];
        }

        self::assertCount(count(array_unique($operationIds)), $operationIds);
    }
}
