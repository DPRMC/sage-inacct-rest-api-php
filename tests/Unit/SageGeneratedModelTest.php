<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Generated\V2026R3\Models\SageObjectsAccountsPayableVendor;
use Dprmc\SageIntacctRestApi\Generated\V2026R3\SageSchemaRegistry;
use PHPUnit\Framework\TestCase;

final class SageGeneratedModelTest extends TestCase
{
    public function test_generated_models_preserve_known_and_unknown_fields(): void
    {
        $vendor = SageObjectsAccountsPayableVendor::fromArray([
            'id' => 'V-1',
            'name' => 'Vendor One',
            'future_sage_field' => ['value' => true],
        ]);

        self::assertSame('V-1', $vendor->get('id'));
        self::assertTrue($vendor->has('future_sage_field'));
        self::assertSame([
            'id' => 'V-1',
            'name' => 'Vendor One',
            'future_sage_field' => ['value' => true],
        ], $vendor->toArray());
    }

    public function test_schema_registry_covers_every_snapshot_schema(): void
    {
        $schemas = SageSchemaRegistry::all();

        self::assertCount(1239, $schemas);
        self::assertArrayHasKey('objects.accounts-payable.vendor', $schemas);
        self::assertSame(
            SageObjectsAccountsPayableVendor::class,
            SageSchemaRegistry::classFor('objects.accounts-payable.vendor'),
        );
    }
}
