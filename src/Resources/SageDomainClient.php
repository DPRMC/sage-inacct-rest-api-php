<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Resources;

use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;

final class SageDomainClient
{
    public function __construct(
        private readonly SageIntacctClient $client,
        private readonly string $domain,
        private readonly bool $allowWrites = false,
    ) {
        if ($this->domain === '') {
            throw new \InvalidArgumentException('A Sage API domain is required.');
        }
    }

    public function object(string $object): SageObjectClient
    {
        return $this->client->object(
            trim($this->domain, '/') . '/' . trim($object, '/'),
            $this->allowWrites,
        );
    }

    public function name(): string
    {
        return $this->domain;
    }
}
