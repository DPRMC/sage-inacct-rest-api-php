<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Resources;

use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use Dprmc\SageIntacctRestApi\Response\SageApiResponse;
use Dprmc\SageIntacctRestApi\Response\SagePaginator;
use Dprmc\SageIntacctRestApi\Exception\SageIntacctException;
use Generator;

final class SageObjectClient
{
    public function __construct(
        private readonly SageIntacctClient $client,
        private readonly string $object,
        private readonly bool $allowWrites = false,
    ) {
        if ($this->object === '') {
            throw new \InvalidArgumentException('A Sage object name is required.');
        }
    }

    /**
     * @param array<string, mixed> $query
     */
    public function get(string $id, array $query = []): SageApiResponse
    {
        return $this->client->get($this->objectPath($id), $query);
    }

    /**
     * @param array<string, mixed> $query
     */
    public function list(array $query = []): SageApiResponse
    {
        return $this->client->get($this->objectPath(), $query);
    }

    /**
     * @param list<string> $fields
     * @param list<array<string, mixed>> $filters
     * @param list<array<string, string>> $orderBy
     * @param array<string, mixed> $filterParameters
     */
    public function query(
        array $fields = [],
        array $filters = [],
        string $filterExpression = 'and',
        array $orderBy = [],
        int $start = 1,
        int $size = 100,
        array $filterParameters = [],
    ): SageApiResponse {
        return $this->client->query(
            object: $this->object,
            fields: $fields,
            filters: $filters,
            filterExpression: $filterExpression,
            orderBy: $orderBy,
            start: $start,
            size: $size,
            filterParameters: $filterParameters,
        );
    }

    /**
     * @param list<string> $fields
     * @param list<array<string, mixed>> $filters
     * @param list<array<string, string>> $orderBy
     * @param array<string, mixed> $filterParameters
     * @return Generator<int, array<string, mixed>>
     */
    public function queryAll(
        array $fields = [],
        array $filters = [],
        string $filterExpression = 'and',
        array $orderBy = [],
        int $pageSize = 100,
        array $filterParameters = [],
    ): Generator {
        yield from SagePaginator::iterate(
            fn (int $start, int $size): SageApiResponse => $this->query(
                fields: $fields,
                filters: $filters,
                filterExpression: $filterExpression,
                orderBy: $orderBy,
                start: $start,
                size: $size,
                filterParameters: $filterParameters,
            ),
            $pageSize,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function create(array $payload): SageApiResponse
    {
        $this->assertWritesAllowed();

        return $this->client->post($this->objectPath(), $payload);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function update(string $id, array $payload): SageApiResponse
    {
        $this->assertWritesAllowed();

        return $this->client->patch($this->objectPath($id), $payload);
    }

    public function delete(string $id): SageApiResponse
    {
        $this->assertWritesAllowed();

        return $this->client->delete($this->objectPath($id));
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function workflow(string $id, string $operation, array $payload = []): SageApiResponse
    {
        $this->assertWritesAllowed();

        return $this->client->post(
            $this->objectPath($id) . '/' . ltrim($operation, '/'),
            $payload,
        );
    }

    public function name(): string
    {
        return $this->object;
    }

    private function objectPath(?string $id = null): string
    {
        $path = '/objects/' . ltrim($this->object, '/');

        if ($id !== null && $id !== '') {
            $path .= '/' . rawurlencode($id);
        }

        return $path;
    }

    private function assertWritesAllowed(): void
    {
        if (!$this->allowWrites) {
            throw new SageIntacctException(
                'Sage object writes are disabled. Create the object client with allowWrites=true to enable them.',
            );
        }
    }
}
