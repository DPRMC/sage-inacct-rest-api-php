<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Response;

final class SageApiResponse
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private readonly array $payload,
        public readonly int $statusCode,
    ) {
    }

    public function result(): mixed
    {
        return $this->payload['ia::result'] ?? $this->payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function meta(): array
    {
        $meta = $this->payload['ia::meta'] ?? [];

        return is_array($meta) ? $meta : [];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->payload;
    }
}
