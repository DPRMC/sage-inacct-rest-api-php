<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Services;

use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use Dprmc\SageIntacctRestApi\Response\SageApiResponse;

final class SageDeliveryQueueClient
{
    public function __construct(private readonly SageIntacctClient $client)
    {
    }

    /**
     * @param array<string, mixed> $query
     */
    public function count(array $query = []): SageApiResponse
    {
        return $this->client->get('/services/delivery/event/count', $query);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function list(array $query = []): SageApiResponse
    {
        return $this->client->get('/services/delivery/event/list', $query);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function acknowledge(array $payload): SageApiResponse
    {
        return $this->client->post('/services/delivery/event/ack', $payload);
    }
}
