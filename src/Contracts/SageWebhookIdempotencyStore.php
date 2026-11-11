<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Contracts;

interface SageWebhookIdempotencyStore
{
    /**
     * Atomically claim a key for processing.
     *
     * Return true only for the first claim. Duplicate deliveries must return false.
     */
    public function claim(string $idempotencyKey): bool;
}
