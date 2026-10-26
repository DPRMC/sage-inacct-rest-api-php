<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Exception;

final class SageIntacctRequestException extends SageIntacctException
{
    /**
     * @param array<string, mixed> $errorPayload
     */
    public function __construct(
        string $message,
        public readonly int $httpStatus,
        public readonly array $errorPayload = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $httpStatus, $previous);
    }
}
