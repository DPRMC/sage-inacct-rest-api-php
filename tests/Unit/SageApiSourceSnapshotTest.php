<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Schema\SageApiSourceSnapshot;
use PHPUnit\Framework\TestCase;

final class SageApiSourceSnapshotTest extends TestCase
{
    public function test_the_pinned_2026_r3_snapshot_is_complete_and_traceable(): void
    {
        $snapshot = SageApiSourceSnapshot::load('2026-r3');

        self::assertSame('2026-r3', $snapshot->release());
        self::assertSame(
            'https://developer.sage.com/intacct/apis/intacct/1/intacct-openapi.md',
            $snapshot->sourceUrl(),
        );
        self::assertSame(1220, $snapshot->pathCount());
        self::assertSame(1239, $snapshot->schemaCount());
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $snapshot->sha256());
        self::assertSame($snapshot->sha256(), hash('sha256', $snapshot->rawJson()));
    }
}
