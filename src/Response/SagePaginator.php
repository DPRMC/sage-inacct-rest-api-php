<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Response;

use Generator;

final class SagePaginator
{
    /**
     * @param callable(int, int): SageApiResponse $fetchPage
     * @return Generator<int, array<string, mixed>>
     */
    public static function iterate(callable $fetchPage, int $pageSize = 100): Generator
    {
        if ($pageSize < 1) {
            throw new \InvalidArgumentException('Sage page size must be greater than zero.');
        }

        $start = 1;

        while (true) {
            $response = $fetchPage($start, $pageSize);
            $result = $response->result();

            if (!is_array($result)) {
                return;
            }

            $returnedCount = 0;

            foreach ($result as $record) {
                if (!is_array($record)) {
                    continue;
                }

                $returnedCount++;
                yield $record;
            }

            if (self::isLastPage($response->meta(), $start, $returnedCount, $pageSize)) {
                return;
            }

            if ($returnedCount === 0) {
                return;
            }

            $start += $returnedCount;
        }
    }

    /**
     * @param array<string, mixed> $meta
     */
    private static function isLastPage(array $meta, int $start, int $returnedCount, int $pageSize): bool
    {
        foreach (['hasMore', 'hasNextPage', 'more'] as $key) {
            if (array_key_exists($key, $meta) && $meta[$key] === false) {
                return true;
            }
        }

        foreach (['hasMore', 'hasNextPage', 'more'] as $key) {
            if (array_key_exists($key, $meta) && $meta[$key] === true) {
                return false;
            }
        }

        foreach (['totalCount', 'total', 'total_count'] as $key) {
            $total = $meta[$key] ?? null;

            if (is_numeric($total)) {
                return $start + $returnedCount - 1 >= (int)$total;
            }
        }

        return $returnedCount < $pageSize;
    }
}
