<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Http;

use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Exception\SageIntacctRequestException;
use Dprmc\SageIntacctRestApi\Response\SageApiResponse;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class SageIntacctClient
{
    private readonly ClientInterface $httpClient;

    private readonly LoggerInterface $logger;

    /**
     * @param array<string, int|float> $retryDelaysInSeconds
     */
    public function __construct(
        private readonly OAuthTokenProvider $tokenProvider,
        private readonly string $baseUrl = 'https://api.intacct.com/ia/api/v1',
        ?ClientInterface $httpClient = null,
        ?LoggerInterface $logger = null,
        private readonly int $maxRetries = 3,
        private readonly array $retryDelaysInSeconds = [1, 2, 4],
        private readonly ?string $entityId = null,
        private readonly bool $allowWrites = false,
    ) {
        $this->httpClient = $httpClient ?? new Client();
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param array<string, mixed> $query
     */
    public function get(string $path, array $query = [], array $headers = []): SageApiResponse
    {
        return $this->send('GET', $path, $query, null, $headers);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function post(string $path, array $payload = [], array $headers = []): SageApiResponse
    {
        return $this->send('POST', $path, [], $payload, $headers);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function put(string $path, array $payload = [], array $headers = []): SageApiResponse
    {
        return $this->send('PUT', $path, [], $payload, $headers);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function patch(string $path, array $payload = [], array $headers = []): SageApiResponse
    {
        return $this->send('PATCH', $path, [], $payload, $headers);
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     */
    public function delete(string $path, array $query = [], array $headers = []): SageApiResponse
    {
        return $this->send('DELETE', $path, $query, null, $headers);
    }

    /**
     * Send a request to any documented Sage REST endpoint.
     *
     * @param array<string, mixed> $query
     * @param array<string, mixed>|null $payload
     * @param array<string, string> $headers
     */
    public function request(
        string $method,
        string $path,
        array $query = [],
        ?array $payload = null,
        array $headers = [],
    ): SageApiResponse {
        return $this->send(strtoupper($method), $path, $query, $payload, $headers);
    }

    /**
     * Query a Sage object using the REST query service.
     *
     * @param list<array<string, mixed>> $filters
     * @param list<string> $fields
     * @param list<array<string, string>> $orderBy
     * @param array<string, mixed> $filterParameters
     */
    public function query(
        string $object,
        array $fields = [],
        array $filters = [],
        string $filterExpression = 'and',
        array $orderBy = [],
        int $start = 1,
        int $size = 100,
        array $filterParameters = [],
    ): SageApiResponse {
        $payload = [
            'object' => $object,
            'fields' => $fields,
            'filters' => $filters,
            'filterExpression' => $filterExpression,
            'orderBy' => $orderBy,
            'start' => $start,
            'size' => $size,
        ];

        if ($filterParameters !== []) {
            $payload['filterParameters'] = $filterParameters;
        }

        return $this->post('/services/core/query', $payload);
    }

    /**
     * Export a Sage object in a supported file format.
     *
     * @param array<string, mixed> $query
     */
    public function export(string $fileType, array $query): SageApiResponse
    {
        return $this->post('/services/core/export', [
            'fileType' => $fileType,
            'query' => $query,
        ]);
    }

    public function object(string $object, ?bool $allowWrites = null): \Dprmc\SageIntacctRestApi\Resources\SageObjectClient
    {
        return new \Dprmc\SageIntacctRestApi\Resources\SageObjectClient(
            $this,
            $object,
            $allowWrites ?? $this->allowWrites,
        );
    }

    public function resources(): \Dprmc\SageIntacctRestApi\Resources\SageIntacctResources
    {
        return new \Dprmc\SageIntacctRestApi\Resources\SageIntacctResources($this);
    }

    public function domain(string $domain, ?bool $allowWrites = null): \Dprmc\SageIntacctRestApi\Resources\SageDomainClient
    {
        return new \Dprmc\SageIntacctRestApi\Resources\SageDomainClient(
            $this,
            $domain,
            $allowWrites ?? $this->allowWrites,
        );
    }

    public function deliveryQueue(): \Dprmc\SageIntacctRestApi\Services\SageDeliveryQueueClient
    {
        return new \Dprmc\SageIntacctRestApi\Services\SageDeliveryQueueClient($this);
    }

    public function withEntity(string $entityId): self
    {
        return new self(
            tokenProvider: $this->tokenProvider,
            baseUrl: $this->baseUrl,
            httpClient: $this->httpClient,
            logger: $this->logger,
            maxRetries: $this->maxRetries,
            retryDelaysInSeconds: $this->retryDelaysInSeconds,
            entityId: $entityId,
            allowWrites: $this->allowWrites,
        );
    }

    /**
     * @param array<string, mixed>|null $payload
     * @param array<string, mixed> $query
     */
    private function send(
        string $method,
        string $path,
        array $query,
        ?array $payload,
        array $headers = [],
    ): SageApiResponse
    {
        $url = $this->buildUrl($path);
        $attempt = 0;
        $forceRefresh = false;

        while (true) {
            $accessToken = $this->tokenProvider->getAccessToken($forceRefresh);
            $forceRefresh = false;

            try {
                $response = $this->httpClient->request(
                    $method,
                    $url,
                    $this->requestOptions($accessToken, $query, $payload, $headers),
                );
            } catch (GuzzleException $exception) {
                if ($attempt < $this->maxRetries) {
                    $this->waitBeforeRetry($attempt);
                    $attempt++;
                    continue;
                }

                throw new SageIntacctRequestException(
                    'The Sage request failed before a response was received.',
                    0,
                    [],
                    $exception,
                );
            }

            if ($response->getStatusCode() === 401 && $attempt === 0) {
                $forceRefresh = true;
                $attempt++;
                continue;
            }

            if ($this->isRetryable($response) && $attempt < $this->maxRetries) {
                $this->waitBeforeRetry($attempt, $response);
                $attempt++;
                continue;
            }

            $decodedPayload = $this->decodeResponse($response);

            if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
                $this->logger->warning('Sage REST request was rejected.', [
                    'method' => $method,
                    'path' => $path,
                    'status' => $response->getStatusCode(),
                ]);

                throw new SageIntacctRequestException(
                    'Sage rejected the REST request.',
                    $response->getStatusCode(),
                    $decodedPayload,
                );
            }

            return new SageApiResponse($decodedPayload, $response->getStatusCode());
        }
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed>|null $payload
     * @return array<string, mixed>
     */
    private function requestOptions(
        string $accessToken,
        array $query,
        ?array $payload,
        array $headers,
    ): array
    {
        $options = [
            'headers' => array_merge([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $accessToken,
            ], $headers),
            'http_errors' => false,
        ];

        if ($this->entityId !== null) {
            $options['headers']['X-IA-API-Param-Entity'] = $this->entityId;
        }

        if ($query !== []) {
            $options['query'] = $query;
        }

        if ($payload !== null) {
            $options['headers']['Content-Type'] = 'application/json';
            $options['json'] = $payload;
        }

        return $options;
    }

    private function buildUrl(string $path): string
    {
        return rtrim($this->baseUrl, '/') . '/' . ltrim($path, '/');
    }

    private function isRetryable(ResponseInterface $response): bool
    {
        return in_array($response->getStatusCode(), [408, 429, 500, 502, 503, 504], true);
    }

    private function waitBeforeRetry(int $attempt, ?ResponseInterface $response = null): void
    {
        $retryAfter = $response?->getHeaderLine('Retry-After');
        $lastDelayIndex = count($this->retryDelaysInSeconds) - 1;
        $defaultDelay = $lastDelayIndex >= 0
            ? $this->retryDelaysInSeconds[$lastDelayIndex]
            : 1;
        $delay = is_numeric($retryAfter)
            ? (float)$retryAfter
            : (float)($this->retryDelaysInSeconds[$attempt] ?? $defaultDelay);

        $this->logger->info('Retrying Sage REST request.', [
            'attempt' => $attempt + 1,
            'delay_seconds' => $delay,
        ]);

        usleep((int)($delay * 1_000_000));
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeResponse(ResponseInterface $response): array
    {
        $contents = trim((string)$response->getBody());

        if ($contents === '') {
            return [];
        }

        try {
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new SageIntacctRequestException(
                'Sage returned a non-JSON REST response.',
                $response->getStatusCode(),
                [],
                $exception,
            );
        }

        if (!is_array($payload)) {
            throw new SageIntacctRequestException(
                'Sage returned an unexpected REST response shape.',
                $response->getStatusCode(),
            );
        }

        return $payload;
    }
}
