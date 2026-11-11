<?php

declare(strict_types=1);

ini_set('memory_limit', '512M');

require dirname(__DIR__) . '/vendor/autoload.php';

use Dprmc\SageIntacctRestApi\Schema\SageApiSourceSnapshot;
use Dprmc\SageIntacctRestApi\Schema\SageEndpointInventory;

$release = $argv[1] ?? '2026-r3';
$outputPath = dirname(__DIR__) . '/resources/generated/' . $release . '/endpoints.json';
$inventory = SageEndpointInventory::fromSnapshot(SageApiSourceSnapshot::load($release));
$catalog = [];

foreach ($inventory->all() as $endpoint) {
    $catalog[] = $endpoint->toArray();
}

$contents = json_encode($catalog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;
$outputDirectory = dirname($outputPath);

if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0775, true) && !is_dir($outputDirectory)) {
    throw new RuntimeException('Could not create the generated endpoint catalog directory.');
}

$bytesWritten = file_put_contents($outputPath, $contents);

if ($bytesWritten === false || $bytesWritten !== strlen($contents)) {
    throw new RuntimeException('Could not write the generated endpoint catalog.');
}

fwrite(STDOUT, sprintf(
    "Generated %s with %d endpoint operations.\n",
    $outputPath,
    count($catalog),
));
