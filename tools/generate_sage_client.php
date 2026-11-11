<?php

declare(strict_types=1);

use Dprmc\SageIntacctRestApi\Schema\SageApiSourceSnapshot;
use Dprmc\SageIntacctRestApi\Schema\SageEndpointInventory;

require dirname(__DIR__) . '/vendor/autoload.php';

$release = '2026-r3';
$snapshot = SageApiSourceSnapshot::load($release);
$inventory = SageEndpointInventory::fromSnapshot($snapshot);
$operations = array_map(
    static fn ($endpoint): array => $endpoint->toArray(),
    $inventory->all(),
);

$methodNames = [];
$methods = [];
$methodMap = [];

foreach ($operations as $operation) {
    $methodName = methodName($operation['operation_id']);

    if (isset($methodNames[$methodName])) {
        throw new RuntimeException(sprintf(
            'Generated method name "%s" is not unique for operation IDs "%s" and "%s".',
            $methodName,
            $methodNames[$methodName],
            $operation['operation_id'],
        ));
    }

    $methodNames[$methodName] = $operation['operation_id'];
    $methodMap[$operation['operation_id']] = $methodName;
    $methods[] = renderMethod($methodName, $operation);
}

$outputDirectory = dirname(__DIR__) . '/src/Generated/V2026R3';

if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0775, true) && !is_dir($outputDirectory)) {
    throw new RuntimeException(sprintf('Unable to create generated client directory "%s".', $outputDirectory));
}

$class = <<<'PHP'
<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Generated\V2026R3;

use Dprmc\SageIntacctRestApi\Http\SageIntacctClient;
use Dprmc\SageIntacctRestApi\Response\SageApiResponse;
use Dprmc\SageIntacctRestApi\Response\SageFileResponse;

final class SageIntacctApiClient
{
    /**
     * @var array<string, string>
     */
    private const OPERATION_METHODS = OPERATION_METHODS_PLACEHOLDER;

    public function __construct(private readonly SageIntacctClient $client)
    {
    }

    public static function methodForOperationId(string $operationId): string
    {
        if (!isset(self::OPERATION_METHODS[$operationId])) {
            throw new \InvalidArgumentException(sprintf(
                'The Sage operation "%s" is not in the generated 2026 R3 client.',
                $operationId,
            ));
        }

        return self::OPERATION_METHODS[$operationId];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function operations(): array
    {
        $path = dirname(__DIR__, 3) . '/resources/generated/2026-r3/endpoints.json';
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new \RuntimeException('The generated Sage endpoint catalog could not be read.');
        }

        $operations = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($operations)) {
            throw new \RuntimeException('The generated Sage endpoint catalog has an invalid shape.');
        }

        return $operations;
    }

    private function path(string $template, array $pathParameters): string
    {
        $path = preg_replace_callback(
            '/\{([^}]+)\}/',
            static function (array $matches) use ($pathParameters): string {
                $parameterName = $matches[1];

                if (!array_key_exists($parameterName, $pathParameters)) {
                    throw new \InvalidArgumentException(sprintf(
                        'The Sage path parameter "%s" is required.',
                        $parameterName,
                    ));
                }

                return rawurlencode((string)$pathParameters[$parameterName]);
            },
            $template,
        );

        if ($path === null) {
            throw new \RuntimeException('Unable to expand the generated Sage endpoint path.');
        }

        return $path;
    }

PHP;

$class .= implode("\n", $methods);
$class .= "\n}\n";

$class = str_replace(
    'OPERATION_METHODS_PLACEHOLDER',
    var_export($methodMap, true),
    $class,
);

$outputPath = $outputDirectory . '/SageIntacctApiClient.php';

if (file_put_contents($outputPath, $class) === false) {
    throw new RuntimeException(sprintf('Unable to write generated Sage client "%s".', $outputPath));
}

printf(
    "Generated %s with %d operation methods.\n",
    $outputPath,
    count($operations),
);

/**
 * @param array<string, mixed> $operation
 */
function renderMethod(string $methodName, array $operation): string
{
    $summary = trim((string)$operation['summary']);
    $summary = str_replace(['*/', "\r", "\n"], ['', ' ', ' '], $summary);
    $method = strtoupper((string)$operation['method']);
    $path = var_export((string)$operation['path'], true);
    $defaultAccept = defaultAcceptHeader($operation['response_media_types'] ?? []);
    $requestHeaders = $defaultAccept === null
        ? '$headers'
        : "array_merge(['Accept' => " . var_export($defaultAccept, true) . "], \$headers)";

    return <<<PHP
    /**
     * {$summary}
     *
     * @param array<string, scalar|null> \$pathParameters
     * @param array<string, mixed> \$query
     * @param array<string, mixed>|null \$payload
     * @param array<string, string> \$headers
     */
    public function {$methodName}(
        array \$pathParameters = [],
        array \$query = [],
        ?array \$payload = null,
        array \$headers = [],
    ): SageApiResponse|SageFileResponse {
        return \$this->client->request(
            method: '{$method}',
            path: \$this->path({$path}, \$pathParameters),
            query: \$query,
            payload: \$payload,
            headers: {$requestHeaders},
        );
    }

PHP;
}

/**
 * @param mixed $mediaTypes
 */
function defaultAcceptHeader(mixed $mediaTypes): ?string
{
    if (!is_array($mediaTypes)) {
        return null;
    }

    foreach ($mediaTypes as $mediaType) {
        if (!is_string($mediaType)) {
            continue;
        }

        $normalized = strtolower($mediaType);

        if ($normalized !== 'application/json' && !str_contains($normalized, '+json')) {
            return $mediaType;
        }
    }

    return null;
}

function methodName(string $operationId): string
{
    $parts = preg_split('/[^A-Za-z0-9]+/', $operationId, -1, PREG_SPLIT_NO_EMPTY);

    if ($parts === false || $parts === []) {
        throw new RuntimeException(sprintf('Operation ID "%s" cannot become a PHP method name.', $operationId));
    }

    $methodName = strtolower((string)array_shift($parts));

    foreach ($parts as $part) {
        $methodName .= ucfirst(strtolower($part));
    }

    $reservedNames = [
        'and', 'array', 'callable', 'case', 'class', 'clone', 'const', 'continue', 'declare',
        'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor',
        'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final',
        'finally', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements',
        'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list',
        'match', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public',
        'readonly', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait',
        'try', 'unset', 'use', 'var', 'while', 'xor', 'yield',
    ];

    if (in_array(strtolower($methodName), $reservedNames, true)) {
        return 'operation' . ucfirst($methodName);
    }

    return $methodName;
}
