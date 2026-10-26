<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Resources\SageEndpointCatalog;
use PHPUnit\Framework\TestCase;

final class SageEndpointCatalogTest extends TestCase
{
    public function test_it_lists_the_documented_sage_domains(): void
    {
        $domains = SageEndpointCatalog::domains();

        self::assertContains('accounts-payable', $domains);
        self::assertContains('general-ledger', $domains);
        self::assertContains('reports', $domains);
        self::assertContains('common-resources', $domains);
    }
}
