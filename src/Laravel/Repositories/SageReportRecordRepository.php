<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Laravel\Repositories;

use Dprmc\SageIntacctRestApi\Laravel\Models\SageReportRecord;

final class SageReportRecordRepository
{
    public function findByReportAndSourceKey(string $reportIdentifier, string $sourceKey): ?SageReportRecord
    {
        return SageReportRecord::query()
            ->where('report_identifier', $reportIdentifier)
            ->where('source_key', $sourceKey)
            ->first();
    }
}
