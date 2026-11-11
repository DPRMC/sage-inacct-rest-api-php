<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Generated;

abstract class SageGeneratedModel
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(protected readonly array $attributes)
    {
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public static function fromArray(array $attributes): static
    {
        return new static($attributes);
    }

    public static function schemaName(): string
    {
        return static::class;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->attributes);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<string, mixed>
     */
    public function raw(): array
    {
        return $this->attributes;
    }
}
