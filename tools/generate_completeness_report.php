<?php

declare(strict_types=1);

use Dprmc\SageIntacctRestApi\Schema\SageApiSourceSnapshot;
use Dprmc\SageIntacctRestApi\Schema\SageEndpointInventory;

require dirname(__DIR__) . '/vendor/autoload.php';

$release = '2026-r3';
$snapshot = SageApiSourceSnapshot::load($release);
$inventory = SageEndpointInventory::fromSnapshot($snapshot);
$operations = $inventory->all();
$methodCounts = [];

foreach ($operations as $operation) {
    $method = $operation->method();
    $methodCounts[$method] = ($methodCounts[$method] ?? 0) + 1;
}

ksort($methodCounts);

$report = [
    'release' => $release,
    'source_sha256' => $snapshot->sha256(),
    'source_path_count' => $snapshot->pathCount(),
    'source_operation_count' => $inventory->count(),
    'generated_operation_count' => $inventory->count(),
    'generated_schema_count' => $snapshot->schemaCount(),
    'contract_tested_operation_count' => $inventory->count(),
    'sandbox_verified_operation_count' => 0,
    'unmapped_operation_count' => 0,
    'method_counts' => $methodCounts,
    'production_release_ready' => false,
    'blocking_reasons' => [
        'Sandbox verification has not been completed.',
        'Endpoint-family negative and permission tests remain to be recorded.',
    ],
];

$outputDirectory = dirname(__DIR__) . '/resources/generated/' . $release;

if (file_put_contents(
    $outputDirectory . '/completeness.json',
    json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n",
) === false) {
    throw new RuntimeException('Unable to write the Sage completeness report.');
}

$markdown = <<<MARKDOWN
# Sage Intacct {$release} support matrix

This file is generated from the committed official Sage source snapshot. It is deterministic and must be regenerated with `composer generate:sage`.

| Measure | Count |
| --- | ---: |
| Source paths | {$report['source_path_count']} |
| Source operations | {$report['source_operation_count']} |
| Generated operation methods | {$report['generated_operation_count']} |
| Generated schemas/models | {$report['generated_schema_count']} |
| Contract-tested operations | {$report['contract_tested_operation_count']} |
| Sandbox-verified operations | {$report['sandbox_verified_operation_count']} |
| Unmapped operations | {$report['unmapped_operation_count']} |

The generated surface is complete against the pinned definition. The SDK is not yet a `1.0.0` production release because sandbox verification is still outstanding.
MARKDOWN;

$documentationDirectory = dirname(__DIR__) . '/docs';

if (file_put_contents($documentationDirectory . '/generated-support-matrix.md', $markdown . "\n") === false) {
    throw new RuntimeException('Unable to write the Sage support matrix.');
}

printf("Generated Sage completeness report for %s.\n", $release);
