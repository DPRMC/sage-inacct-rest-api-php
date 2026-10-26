<?php

declare(strict_types=1);

namespace Tests\Unit;

use Dprmc\SageIntacctRestApi\Laravel\Repositories\SageReportRecordRepository;
use Dprmc\SageIntacctRestApi\Laravel\Repositories\SageReportRunRepository;
use Dprmc\SageIntacctRestApi\Laravel\Writers\SageReportRecordWriter;
use Dprmc\SageIntacctRestApi\Laravel\Writers\SageReportRunWriter;
use Illuminate\Database\Capsule\Manager as Capsule;
use PHPUnit\Framework\TestCase;

final class LaravelPersistenceTest extends TestCase
{
    private Capsule $capsule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->capsule = new Capsule();
        $this->capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
        $this->capsule->setAsGlobal();
        $this->capsule->bootEloquent();

        $schema = $this->capsule->schema();
        $schema->create('sage_intacct_report_runs', function ($table): void {
            $table->increments('id');
            $table->string('report_identifier');
            $table->string('report_name')->nullable();
            $table->string('status');
            $table->dateTime('requested_at');
            $table->dateTime('completed_at')->nullable();
            $table->string('request_checksum');
            $table->string('response_checksum')->nullable();
            $table->text('request_payload');
            $table->string('parser_version');
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
        $schema->create('sage_intacct_report_records', function ($table): void {
            $table->increments('id');
            $table->unsignedInteger('run_id');
            $table->string('report_identifier');
            $table->string('source_key');
            $table->string('source_checksum');
            $table->text('source_payload');
            $table->text('normalized_payload')->nullable();
            $table->timestamps();
        });
    }

    public function test_report_writers_and_repositories_round_trip_data(): void
    {
        $runWriter = new SageReportRunWriter();
        $run = $runWriter->start('five-favorited-reports', 'Vendor report', ['period' => '2026-10'], 'v1');

        $recordWriter = new SageReportRecordWriter();
        $recordWriter->write(
            runId: $run->id,
            reportIdentifier: 'five-favorited-reports',
            sourceKey: 'vendor-1',
            sourcePayload: ['id' => 'vendor-1', 'name' => 'Vendor One'],
            normalizedPayload: ['vendor_id' => 'vendor-1'],
        );

        $runFromRepository = (new SageReportRunRepository())->findLatestForReport('five-favorited-reports');
        $recordFromRepository = (new SageReportRecordRepository())->findByReportAndSourceKey(
            'five-favorited-reports',
            'vendor-1',
        );

        self::assertNotNull($runFromRepository);
        self::assertSame('running', $runFromRepository->status);
        self::assertNotNull($recordFromRepository);
        self::assertSame(['vendor_id' => 'vendor-1'], $recordFromRepository->normalized_payload);
    }
}
