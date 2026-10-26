<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Resources;

final class SageEndpointCatalog
{
    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        $path = dirname(__DIR__, 2) . '/resources/sage-intacct-api-v1-endpoints.json';
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException('The Sage Intacct endpoint catalog could not be read.');
        }

        $catalog = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($catalog)) {
            throw new \RuntimeException('The Sage Intacct endpoint catalog has an invalid shape.');
        }

        return $catalog;
    }

    /**
     * @return list<string>
     */
    public static function domains(): array
    {
        $domains = self::all()['domains'] ?? [];

        if (!is_array($domains)) {
            return [];
        }

        return array_values(array_filter(array_keys($domains), 'is_string'));
    }
}
