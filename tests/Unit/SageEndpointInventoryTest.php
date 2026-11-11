<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Schema\SageApiSourceSnapshot;
use Dprmc\SageIntacctRestApi\Schema\SageEndpointInventory;
use PHPUnit\Framework\TestCase;

final class SageEndpointInventoryTest extends TestCase
{
    public function test_it_normalizes_every_documented_operation(): void
    {
        $snapshot = SageApiSourceSnapshot::load('2026-r3');
        $inventory = SageEndpointInventory::fromSnapshot($snapshot);

        self::assertSame(2217, $inventory->count());

        $query = $inventory->find('POST', '/services/core/query');

        self::assertSame('post-services-query', $query->operationId());
        self::assertFalse($query->isMutation());
        self::assertSame('safe_read', $query->retryClass());
        self::assertContains('application/json', $query->requestMediaTypes());

        $workflow = $inventory->find('POST', '/workflows/accounts-payable/adjustment/submit');

        self::assertTrue($workflow->isMutation());
        self::assertSame('never', $workflow->retryClass());
        self::assertTrue($workflow->requiresIdempotencyForRetry());

        $download = $inventory->find('GET', '/services/reports/download');

        self::assertContains('application/pdf', $download->responseMediaTypes());
        self::assertFalse($download->isMutation());
    }
}
