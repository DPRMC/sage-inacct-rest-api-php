<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Laravel\Writers;

use Dprmc\SageIntacctRestApi\Laravel\Models\SageReportRun;
use Illuminate\Support\Carbon;

final class SageReportRunWriter
{
    /**
     * @param array<string, mixed> $requestPayload
     */
    public function start(
        string $reportIdentifier,
        ?string $reportName,
        array $requestPayload,
        string $parserVersion,
    ): SageReportRun {
        $run = new SageReportRun();
        $run->report_identifier = $reportIdentifier;
        $run->report_name = $reportName;
        $run->status = 'running';
        $run->requested_at = Carbon::now();
        $run->request_checksum = hash('sha256', json_encode($requestPayload, JSON_THROW_ON_ERROR));
        $run->request_payload = $requestPayload;
        $run->parser_version = $parserVersion;
        $run->save();

        return $run;
    }

    public function complete(SageReportRun $run, string $responseChecksum): SageReportRun
    {
        $run->status = 'completed';
        $run->completed_at = Carbon::now();
        $run->response_checksum = $responseChecksum;
        $run->error_message = null;
        $run->save();

        return $run;
    }

    public function fail(SageReportRun $run, string $errorMessage): SageReportRun
    {
        $run->status = 'failed';
        $run->completed_at = Carbon::now();
        $run->error_message = $errorMessage;
        $run->save();

        return $run;
    }
}
