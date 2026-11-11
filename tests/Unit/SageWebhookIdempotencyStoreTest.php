<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Services\InMemorySageWebhookIdempotencyStore;
use PHPUnit\Framework\TestCase;

final class SageWebhookIdempotencyStoreTest extends TestCase
{
    public function test_only_the_first_delivery_claim_succeeds(): void
    {
        $store = new InMemorySageWebhookIdempotencyStore();

        self::assertTrue($store->claim('delivery-1'));
        self::assertFalse($store->claim('delivery-1'));
        self::assertTrue($store->claim('delivery-2'));
    }
}
