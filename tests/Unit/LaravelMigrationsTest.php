<?php

declare(strict_types=1);

namespace Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\TestCase;

final class LaravelMigrationsTest extends TestCase
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

        $container = new Container();
        Container::setInstance($container);
        $container->instance('db', $this->capsule->getDatabaseManager());
        $container->instance('db.schema', $this->capsule->schema());
        Facade::setFacadeApplication($container);
    }

    public function test_package_migrations_create_the_expected_tables(): void
    {
        $migrationFiles = glob(__DIR__ . '/../../database/migrations/*.php');
        self::assertIsArray($migrationFiles);
        self::assertCount(3, $migrationFiles);

        foreach ($migrationFiles as $migrationFile) {
            $migration = require $migrationFile;
            $migration->up();
        }

        self::assertTrue(Schema::hasTable('sage_intacct_oauth_tokens'));
        self::assertTrue(Schema::hasTable('sage_intacct_report_runs'));
        self::assertTrue(Schema::hasTable('sage_intacct_report_records'));
        self::assertTrue(Schema::hasColumn('sage_intacct_report_records', 'normalized_payload'));
    }
}
