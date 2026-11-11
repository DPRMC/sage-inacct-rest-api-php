<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Services;

use Dprmc\SageIntacctRestApi\Contracts\SageWebhookIdempotencyStore;

final class InMemorySageWebhookIdempotencyStore implements SageWebhookIdempotencyStore
{
    /**
     * @var array<string, true>
     */
    private array $claimedKeys = [];

    public function claim(string $idempotencyKey): bool
    {
        if ($idempotencyKey === '') {
            throw new \InvalidArgumentException('A Sage webhook idempotency key is required.');
        }

        if (isset($this->claimedKeys[$idempotencyKey])) {
            return false;
        }

        $this->claimedKeys[$idempotencyKey] = true;

        return true;
    }
}
