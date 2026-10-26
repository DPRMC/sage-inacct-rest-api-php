<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Laravel\Repositories;

use Dprmc\SageIntacctRestApi\Laravel\Models\SageReportRun;

final class SageReportRunRepository
{
    public function find(int $id): ?SageReportRun
    {
        return SageReportRun::query()->find($id);
    }

    public function findLatestForReport(string $reportIdentifier): ?SageReportRun
    {
        return SageReportRun::query()
            ->where('report_identifier', $reportIdentifier)
            ->latest('requested_at')
            ->first();
    }
}
