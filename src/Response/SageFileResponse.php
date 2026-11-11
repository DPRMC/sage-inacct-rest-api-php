<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Response;

final class SageFileResponse
{
    /**
     * @param array<string, list<string>> $headers
     */
    public function __construct(
        private readonly string $body,
        public readonly int $statusCode,
        private readonly array $headers = [],
    ) {
    }

    public function body(): string
    {
        return $this->body;
    }

    public function contentType(): ?string
    {
        return $this->header('Content-Type');
    }

    public function header(string $name): ?string
    {
        foreach ($this->headers as $headerName => $values) {
            if (strcasecmp($headerName, $name) !== 0) {
                continue;
            }

            return $values[0] ?? null;
        }

        return null;
    }

    /**
     * @return array<string, list<string>>
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function saveTo(string $path): void
    {
        $bytesWritten = file_put_contents($path, $this->body);

        if ($bytesWritten === false) {
            throw new \RuntimeException(sprintf('Unable to save Sage response to "%s".', $path));
        }
    }
}
