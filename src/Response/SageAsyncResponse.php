<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Response;

final class SageAsyncResponse extends SageApiResponse
{
    public function __construct(
        array $payload,
        int $statusCode,
        public readonly ?string $location = null,
    ) {
        parent::__construct($payload, $statusCode);
    }

    public function isAccepted(): bool
    {
        return $this->statusCode === 202;
    }
}
