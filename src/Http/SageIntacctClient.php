<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Http;

use Dprmc\SageIntacctRestApi\Auth\OAuthTokenProvider;
use Dprmc\SageIntacctRestApi\Exception\SageIntacctRequestException;
use Dprmc\SageIntacctRestApi\Generated\V2026R3\SageIntacctApiClient;
use Dprmc\SageIntacctRestApi\Response\SageApiResponse;
use Dprmc\SageIntacctRestApi\Response\SageAsyncResponse;
use Dprmc\SageIntacctRestApi\Response\SageFileResponse;
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
        private readonly float $maxRetryDelayInSeconds = 60.0,
        private readonly ?string $entityId = null,
        private readonly bool $allowWrites = false,
    ) {
        $this->httpClient = $httpClient ?? new Client();
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * @param array<string, mixed> $query
     */
    public function get(string $path, array $query = [], array $headers = []): SageApiResponse|SageFileResponse
    {
        return $this->send('GET', $path, $query, null, $headers);
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function post(string $path, array $payload = [], array $headers = []): SageApiResponse|SageFileResponse
    {
        return $this->send('POST', $path, [], $payload, $headers);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function put(string $path, array $payload = [], array $headers = []): SageApiResponse|SageFileResponse
    {
        return $this->send('PUT', $path, [], $payload, $headers);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function patch(string $path, array $payload = [], array $headers = []): SageApiResponse|SageFileResponse
    {
        return $this->send('PATCH', $path, [], $payload, $headers);
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, string> $headers
     */
    public function delete(string $path, array $query = [], array $headers = []): SageApiResponse|SageFileResponse
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
    ): SageApiResponse|SageFileResponse {
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

        $response = $this->post('/services/core/query', $payload);

        if ($response instanceof SageFileResponse) {
            throw new SageIntacctRequestException(
                'Sage returned a file response for a query request.',
                $response->statusCode,
            );
        }

        return $response;
    }

    /**
     * Export a Sage object in a supported file format.
     *
     * @param array<string, mixed> $query
     */
    public function export(string $fileType, array $query): SageApiResponse|SageFileResponse
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

    public function generated(): SageIntacctApiClient
    {
        return new SageIntacctApiClient($this);
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
            maxRetryDelayInSeconds: $this->maxRetryDelayInSeconds,
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
    ): SageApiResponse|SageFileResponse
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
                if ($this->canRetry($method, $path, $headers) && $attempt < $this->maxRetries) {
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

            if (
                $this->isRetryable($response)
                && $this->canRetry($method, $path, $headers)
                && $attempt < $this->maxRetries
            ) {
                $this->waitBeforeRetry($attempt, $response);
                $attempt++;
                continue;
            }

            $isJsonResponse = $this->isJsonResponse($response);
            $decodedPayload = $isJsonResponse
                ? $this->decodeResponse($response)
                : [];

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
                    errorCode: $this->errorCode($decodedPayload),
                    requestId: $this->requestId($response),
                    retryAfterSeconds: $this->retryAfterSeconds($response),
                );
            }

            if (!$isJsonResponse) {
                return new SageFileResponse(
                    body: (string)$response->getBody(),
                    statusCode: $response->getStatusCode(),
                    headers: $response->getHeaders(),
                );
            }

            if ($response->getStatusCode() === 202) {
                return new SageAsyncResponse(
                    payload: $decodedPayload,
                    statusCode: $response->getStatusCode(),
                    location: $response->getHeaderLine('Location') !== ''
                        ? $response->getHeaderLine('Location')
                        : null,
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

    /**
     * Mutations are retried only when the caller provides an idempotency key.
     * Sage's query and export services use POST for read operations.
     *
     * @param array<string, string> $headers
     */
    private function canRetry(string $method, string $path, array $headers): bool
    {
        $normalizedMethod = strtoupper($method);
        $normalizedPath = '/' . ltrim($path, '/');

        if (in_array($normalizedMethod, ['GET', 'HEAD', 'OPTIONS'], true)) {
            return true;
        }

        if (
            $normalizedMethod === 'POST'
            && in_array($normalizedPath, ['/services/core/query', '/services/core/export'], true)
        ) {
            return true;
        }

        return $this->hasNonEmptyHeader($headers, 'Idempotency-Key');
    }

    /**
     * @param array<string, string> $headers
     */
    private function hasNonEmptyHeader(array $headers, string $name): bool
    {
        foreach ($headers as $headerName => $headerValue) {
            if (strcasecmp($headerName, $name) === 0 && trim($headerValue) !== '') {
                return true;
            }
        }

        return false;
    }

    private function isJsonResponse(ResponseInterface $response): bool
    {
        $contentType = strtolower($response->getHeaderLine('Content-Type'));

        if ($contentType === '') {
            return true;
        }

        return str_contains($contentType, 'application/json')
            || str_contains($contentType, '+json');
    }

    private function waitBeforeRetry(int $attempt, ?ResponseInterface $response = null): void
    {
        $retryAfter = $response?->getHeaderLine('Retry-After');
        $lastDelayIndex = count($this->retryDelaysInSeconds) - 1;
        $defaultDelay = $lastDelayIndex >= 0
            ? $this->retryDelaysInSeconds[$lastDelayIndex]
            : 1;
        $hasRetryAfter = is_numeric($retryAfter);
        $delay = $hasRetryAfter
            ? (float)$retryAfter
            : (float)($this->retryDelaysInSeconds[$attempt] ?? $defaultDelay);
        $delay = max(0.0, min($delay, $this->maxRetryDelayInSeconds));

        if (!$hasRetryAfter && $delay > 0.0) {
            $jitter = min($delay * 0.25, 1.0);
            $delay = min(
                $this->maxRetryDelayInSeconds,
                $delay + (random_int(0, 1_000_000) / 1_000_000) * $jitter,
            );
        }

        $this->logger->info('Retrying Sage REST request.', [
            'attempt' => $attempt + 1,
            'delay_seconds' => $delay,
        ]);

        usleep((int)($delay * 1_000_000));
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function errorCode(array $payload): ?string
    {
        $candidates = [
            $payload['ia::error']['code'] ?? null,
            $payload['error']['code'] ?? null,
            $payload['code'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                return $candidate;
            }
        }

        return null;
    }

    private function requestId(ResponseInterface $response): ?string
    {
        foreach (['X-Request-ID', 'X-Request-Id', 'Request-Id'] as $headerName) {
            $requestId = trim($response->getHeaderLine($headerName));

            if ($requestId !== '') {
                return $requestId;
            }
        }

        return null;
    }

    private function retryAfterSeconds(ResponseInterface $response): ?float
    {
        $retryAfter = trim($response->getHeaderLine('Retry-After'));

        if ($retryAfter === '') {
            return null;
        }

        if (is_numeric($retryAfter)) {
            return max(0.0, min((float)$retryAfter, $this->maxRetryDelayInSeconds));
        }

        $retryAt = strtotime($retryAfter);

        if ($retryAt === false) {
            return null;
        }

        return max(0.0, min((float)($retryAt - time()), $this->maxRetryDelayInSeconds));
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
