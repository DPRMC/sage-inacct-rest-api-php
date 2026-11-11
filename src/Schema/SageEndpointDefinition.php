<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Schema;

final class SageEndpointDefinition
{
    /**
     * @param array<string, mixed> $operation
     */
    public static function fromOpenApi(string $method, string $path, array $operation): self
    {
        $operationId = $operation['operationId'] ?? null;

        if (!is_string($operationId) || $operationId === '') {
            throw new \RuntimeException(sprintf(
                'The Sage endpoint has no operationId: %s %s.',
                $method,
                $path,
            ));
        }

        $requestMediaTypes = self::contentTypes($operation['requestBody']['content'] ?? []);
        $responseMediaTypes = [];

        foreach ($operation['responses'] ?? [] as $response) {
            if (!is_array($response)) {
                continue;
            }

            foreach (self::contentTypes($response['content'] ?? []) as $mediaType) {
                if (!in_array($mediaType, $responseMediaTypes, true)) {
                    $responseMediaTypes[] = $mediaType;
                }
            }
        }

        $tags = self::stringList($operation['tags'] ?? []);
        $securitySchemes = [];

        foreach ($operation['security'] ?? [] as $securityRequirement) {
            if (!is_array($securityRequirement)) {
                continue;
            }

            foreach (array_keys($securityRequirement) as $securityScheme) {
                if (is_string($securityScheme) && !in_array($securityScheme, $securitySchemes, true)) {
                    $securitySchemes[] = $securityScheme;
                }
            }
        }

        $mutation = self::classifyMutation($method, $path);

        return new self(
            method: $method,
            path: $path,
            operationId: $operationId,
            summary: is_string($operation['summary'] ?? null) ? $operation['summary'] : null,
            requestMediaTypes: $requestMediaTypes,
            responseMediaTypes: $responseMediaTypes,
            tags: $tags,
            securitySchemes: $securitySchemes,
            mutation: $mutation,
            destructive: self::classifyDestructive($method, $path),
            asynchronous: self::classifyAsynchronous($operation),
            retryClass: $mutation ? 'never' : 'safe_read',
        );
    }

    /**
     * @param list<string> $requestMediaTypes
     * @param list<string> $responseMediaTypes
     * @param list<string> $tags
     * @param list<string> $securitySchemes
     */
    public function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly string $operationId,
        private readonly ?string $summary,
        private readonly array $requestMediaTypes,
        private readonly array $responseMediaTypes,
        private readonly array $tags,
        private readonly array $securitySchemes,
        private readonly bool $mutation,
        private readonly bool $destructive,
        private readonly bool $asynchronous,
        private readonly string $retryClass,
    ) {
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function operationId(): string
    {
        return $this->operationId;
    }

    public function summary(): ?string
    {
        return $this->summary;
    }

    /**
     * @return list<string>
     */
    public function requestMediaTypes(): array
    {
        return $this->requestMediaTypes;
    }

    /**
     * @return list<string>
     */
    public function responseMediaTypes(): array
    {
        return $this->responseMediaTypes;
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return $this->tags;
    }

    /**
     * @return list<string>
     */
    public function securitySchemes(): array
    {
        return $this->securitySchemes;
    }

    public function isMutation(): bool
    {
        return $this->mutation;
    }

    public function isDestructive(): bool
    {
        return $this->destructive;
    }

    public function isAsynchronous(): bool
    {
        return $this->asynchronous;
    }

    public function retryClass(): string
    {
        return $this->retryClass;
    }

    public function requiresIdempotencyForRetry(): bool
    {
        return $this->mutation;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'path' => $this->path,
            'operation_id' => $this->operationId,
            'summary' => $this->summary,
            'request_media_types' => $this->requestMediaTypes,
            'response_media_types' => $this->responseMediaTypes,
            'tags' => $this->tags,
            'security_schemes' => $this->securitySchemes,
            'is_mutation' => $this->mutation,
            'is_destructive' => $this->destructive,
            'is_asynchronous' => $this->asynchronous,
            'retry_class' => $this->retryClass,
            'requires_idempotency_for_retry' => $this->requiresIdempotencyForRetry(),
        ];
    }

    /**
     * @param mixed $content
     * @return list<string>
     */
    private static function contentTypes(mixed $content): array
    {
        if (!is_array($content)) {
            return [];
        }

        return self::stringList(array_keys($content));
    }

    /**
     * @param mixed $values
     * @return list<string>
     */
    private static function stringList(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        $strings = [];

        foreach ($values as $value) {
            if (is_string($value)) {
                $strings[] = $value;
            }
        }

        return array_values(array_unique($strings));
    }

    private static function classifyMutation(string $method, string $path): bool
    {
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return false;
        }

        return !in_array($path, ['/services/core/query', '/services/core/export'], true);
    }

    private static function classifyDestructive(string $method, string $path): bool
    {
        if ($method === 'DELETE') {
            return true;
        }

        return preg_match('/\/(cancel|delete|decline|reverse|revoke|void)(?:\/|$)/i', $path) === 1;
    }

    /**
     * @param array<string, mixed> $operation
     */
    private static function classifyAsynchronous(array $operation): bool
    {
        foreach (array_keys($operation['responses'] ?? []) as $statusCode) {
            if ((string)$statusCode === '202') {
                return true;
            }
        }

        return false;
    }
}
