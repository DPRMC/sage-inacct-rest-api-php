<?php

declare(strict_types=1);

ini_set('memory_limit', '512M');

require dirname(__DIR__) . '/vendor/autoload.php';

use GuzzleHttp\Client;

$release = $argv[1] ?? '2026-r3';
$sourceUrl = $argv[2] ?? 'https://developer.sage.com/intacct/apis/intacct/1/intacct-openapi.md';
$snapshotDirectory = dirname(__DIR__) . '/resources/snapshots/' . $release;

$httpClient = new Client([
    'headers' => [
        'Accept' => 'text/markdown, application/json',
        'User-Agent' => 'dprmc-sage-inacct-rest-api-source-snapshot/1.0',
    ],
    'timeout' => 60,
]);

$response = $httpClient->get($sourceUrl);
$sourceContents = trim((string)$response->getBody());
$jsonContents = stripMarkdownFence($sourceContents);

try {
    $document = json_decode($jsonContents, true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $exception) {
    throw new RuntimeException('Sage returned an invalid OpenAPI document.', 0, $exception);
}

if (!is_array($document)) {
    throw new RuntimeException('Sage returned an invalid OpenAPI document shape.');
}

$paths = $document['paths'] ?? null;
$schemas = $document['components']['schemas'] ?? null;

if (!is_array($paths) || !is_array($schemas)) {
    throw new RuntimeException('The Sage OpenAPI document is missing paths or schemas.');
}

$normalizedJson = json_encode(
    $document,
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
) . PHP_EOL;

$metadata = [
    'release' => $release,
    'source_url' => $sourceUrl,
    'retrieved_at' => gmdate(DATE_ATOM),
    'path_count' => count($paths),
    'schema_count' => count($schemas),
    'sha256' => hash('sha256', $normalizedJson),
];

if (!is_dir($snapshotDirectory) && !mkdir($snapshotDirectory, 0775, true) && !is_dir($snapshotDirectory)) {
    throw new RuntimeException('Could not create the Sage API snapshot directory.');
}

writeFile($snapshotDirectory . '/sage-intacct-api.json', $normalizedJson);
writeFile(
    $snapshotDirectory . '/metadata.json',
    json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL,
);

fwrite(STDOUT, sprintf(
    "Captured Sage %s: %d paths, %d schemas, sha256=%s\n",
    $release,
    count($paths),
    count($schemas),
    $metadata['sha256'],
));

function stripMarkdownFence(string $contents): string
{
    if (!str_starts_with($contents, '```')) {
        return $contents;
    }

    $lines = explode("\n", $contents);
    array_shift($lines);

    if ($lines !== [] && trim((string)end($lines)) === '```') {
        array_pop($lines);
    }

    return trim(implode("\n", $lines));
}

function writeFile(string $path, string $contents): void
{
    $bytesWritten = file_put_contents($path, $contents);

    if ($bytesWritten === false || $bytesWritten !== strlen($contents)) {
        throw new RuntimeException('Could not write the Sage API snapshot file: ' . $path);
    }
}
