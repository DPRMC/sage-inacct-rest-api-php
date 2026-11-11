<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Schema;

final class SageEndpointInventory
{
    /**
     * @param list<SageEndpointDefinition> $endpoints
     */
    private function __construct(private readonly array $endpoints)
    {
    }

    public static function fromSnapshot(SageApiSourceSnapshot $snapshot): self
    {
        $document = $snapshot->document();
        $paths = $document['paths'] ?? null;

        if (!is_array($paths)) {
            throw new \RuntimeException('The Sage API snapshot has no paths.');
        }

        $endpoints = [];
        $supportedMethods = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head', 'trace'];
        $pathItemMetadata = ['$ref', 'description', 'parameters', 'servers', 'summary'];

        foreach ($paths as $path => $pathItem) {
            if (!is_string($path) || !is_array($pathItem)) {
                continue;
            }

            foreach (array_keys($pathItem) as $pathItemKey) {
                if (!is_string($pathItemKey)) {
                    continue;
                }

                if (!in_array($pathItemKey, $supportedMethods, true) && !in_array($pathItemKey, $pathItemMetadata, true)) {
                    throw new \RuntimeException(sprintf(
                        'The Sage API snapshot contains an unsupported path item key "%s" at "%s".',
                        $pathItemKey,
                        $path,
                    ));
                }
            }

            foreach ($supportedMethods as $method) {
                $operation = $pathItem[$method] ?? null;

                if (!is_array($operation)) {
                    continue;
                }

                $endpoints[] = SageEndpointDefinition::fromOpenApi(
                    method: strtoupper($method),
                    path: $path,
                    operation: $operation,
                );
            }
        }

        return new self($endpoints);
    }

    public function count(): int
    {
        return count($this->endpoints);
    }

    public function find(string $method, string $path): SageEndpointDefinition
    {
        $normalizedMethod = strtoupper($method);

        foreach ($this->endpoints as $endpoint) {
            if ($endpoint->method() === $normalizedMethod && $endpoint->path() === $path) {
                return $endpoint;
            }
        }

        throw new \InvalidArgumentException(sprintf(
            'The Sage endpoint was not found: %s %s.',
            $normalizedMethod,
            $path,
        ));
    }

    /**
     * @return list<SageEndpointDefinition>
     */
    public function all(): array
    {
        return $this->endpoints;
    }
}
