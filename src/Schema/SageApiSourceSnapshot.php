<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Schema;

final class SageApiSourceSnapshot
{
    /**
     * @param array<string, mixed> $document
     * @param array<string, mixed> $metadata
     */
    private function __construct(
        private readonly string $release,
        private readonly string $sourceUrl,
        private readonly string $rawJson,
        private readonly array $document,
        private readonly array $metadata,
    ) {
    }

    public static function load(string $release): self
    {
        if ($release === '') {
            throw new \InvalidArgumentException('A Sage API release is required.');
        }

        $snapshotDirectory = dirname(__DIR__, 2) . '/resources/snapshots/' . $release;
        $metadataPath = $snapshotDirectory . '/metadata.json';
        $schemaPath = $snapshotDirectory . '/sage-intacct-api.json';

        $metadata = self::readJsonFile($metadataPath, 'metadata');
        $rawJson = self::readFile($schemaPath, 'API schema');
        $document = self::decodeJson($rawJson, 'API schema');

        self::assertMetadataMatchesDocument($release, $metadata, $document, $rawJson);

        return new self(
            release: $release,
            sourceUrl: self::requiredString($metadata, 'source_url'),
            rawJson: $rawJson,
            document: $document,
            metadata: $metadata,
        );
    }

    public function release(): string
    {
        return $this->release;
    }

    public function sourceUrl(): string
    {
        return $this->sourceUrl;
    }

    public function pathCount(): int
    {
        return count($this->document['paths'] ?? []);
    }

    public function schemaCount(): int
    {
        return count($this->document['components']['schemas'] ?? []);
    }

    public function sha256(): string
    {
        return hash('sha256', $this->rawJson);
    }

    public function rawJson(): string
    {
        return $this->rawJson;
    }

    /**
     * @return array<string, mixed>
     */
    public function document(): array
    {
        return $this->document;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return array<string, mixed>
     */
    private static function readJsonFile(string $path, string $description): array
    {
        $contents = self::readFile($path, $description);

        return self::decodeJson($contents, $description);
    }

    private static function readFile(string $path, string $description): string
    {
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException('The Sage API ' . $description . ' could not be read: ' . $path);
        }

        return $contents;
    }

    /**
     * @return array<string, mixed>
     */
    private static function decodeJson(string $contents, string $description): array
    {
        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException(
                'The Sage API ' . $description . ' is not valid JSON.',
                0,
                $exception,
            );
        }

        if (!is_array($decoded)) {
            throw new \RuntimeException('The Sage API ' . $description . ' has an invalid shape.');
        }

        return $decoded;
    }

    /**
     * @param array<string, mixed> $metadata
     * @param array<string, mixed> $document
     */
    private static function assertMetadataMatchesDocument(
        string $release,
        array $metadata,
        array $document,
        string $rawJson,
    ): void {
        if (($metadata['release'] ?? null) !== $release) {
            throw new \RuntimeException('The Sage API snapshot metadata has the wrong release.');
        }

        $pathCount = count($document['paths'] ?? []);
        $schemaCount = count($document['components']['schemas'] ?? []);

        if (($metadata['path_count'] ?? null) !== $pathCount) {
            throw new \RuntimeException('The Sage API snapshot path count does not match its metadata.');
        }

        if (($metadata['schema_count'] ?? null) !== $schemaCount) {
            throw new \RuntimeException('The Sage API snapshot schema count does not match its metadata.');
        }

        if (($metadata['sha256'] ?? null) !== hash('sha256', $rawJson)) {
            throw new \RuntimeException('The Sage API snapshot checksum does not match its metadata.');
        }
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private static function requiredString(array $metadata, string $key): string
    {
        $value = $metadata[$key] ?? null;

        if (!is_string($value) || $value === '') {
            throw new \RuntimeException('The Sage API snapshot metadata is missing ' . $key . '.');
        }

        return $value;
    }
}
