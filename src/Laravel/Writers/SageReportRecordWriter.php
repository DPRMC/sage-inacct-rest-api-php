<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Laravel\Writers;

use Dprmc\SageIntacctRestApi\Laravel\Models\SageReportRecord;

final class SageReportRecordWriter
{
    /**
     * @param array<string, mixed> $sourcePayload
     * @param array<string, mixed>|null $normalizedPayload
     */
    public function write(
        int $runId,
        string $reportIdentifier,
        string $sourceKey,
        array $sourcePayload,
        ?array $normalizedPayload = null,
    ): SageReportRecord {
        $record = SageReportRecord::query()
            ->where('report_identifier', $reportIdentifier)
            ->where('source_key', $sourceKey)
            ->first();

        if ($record === null) {
            $record = new SageReportRecord();
            $record->report_identifier = $reportIdentifier;
            $record->source_key = $sourceKey;
        }

        $record->run_id = $runId;
        $record->source_checksum = hash('sha256', json_encode($sourcePayload, JSON_THROW_ON_ERROR));
        $record->source_payload = $sourcePayload;
        $record->normalized_payload = $normalizedPayload;
        $record->save();

        return $record;
    }
}
