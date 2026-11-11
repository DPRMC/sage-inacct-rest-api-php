<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Services;

use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use Dprmc\SageIntacctRestApi\Response\SageApiResponse;
use Dprmc\SageIntacctRestApi\Response\SageFileResponse;

final class SageDeliveryQueueClient
{
    public function __construct(private readonly SageIntacctClient $client)
    {
    }

    /**
     * @param array<string, mixed> $query
     */
    public function count(array $query = []): SageApiResponse|SageFileResponse
    {
        return $this->client->get('/services/delivery/event/count', $query);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function list(array $query = []): SageApiResponse|SageFileResponse
    {
        return $this->client->get('/services/delivery/event/list', $query);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function acknowledge(array $payload): SageApiResponse|SageFileResponse
    {
        return $this->client->post('/services/delivery/event/ack', $payload);
    }
}
