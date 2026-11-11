<?php

declare(strict_types=1);

use Dprmc\SageIntacctRestApi\Schema\SageApiSourceSnapshot;

require dirname(__DIR__) . '/vendor/autoload.php';

$release = '2026-r3';
$snapshot = SageApiSourceSnapshot::load($release);
$schemas = $snapshot->document()['components']['schemas'] ?? null;

if (!is_array($schemas)) {
    throw new RuntimeException('The Sage API snapshot has no component schemas.');
}

$modelDirectory = dirname(__DIR__) . '/src/Generated/V2026R3/Models';

if (!is_dir($modelDirectory) && !mkdir($modelDirectory, 0775, true) && !is_dir($modelDirectory)) {
    throw new RuntimeException(sprintf('Unable to create generated model directory "%s".', $modelDirectory));
}

$classMap = [];
$metadata = [];

foreach ($schemas as $schemaName => $schema) {
    if (!is_string($schemaName) || !is_array($schema)) {
        continue;
    }

    $className = modelClassName($schemaName);

    if (isset($classMap[$schemaName]) || in_array($className, $classMap, true)) {
        throw new RuntimeException(sprintf('Generated schema class name is not unique: %s.', $schemaName));
    }

    $classMap[$schemaName] = $className;
    $metadata[$schemaName] = normalizeSchema($schema);

    $class = <<<PHP
<?php

declare(strict_types=1);

namespace Dprmc\\SageIntacctRestApi\\Generated\\V2026R3\\Models;

use Dprmc\\SageIntacctRestApi\\Generated\\SageGeneratedModel;

final class {$className} extends SageGeneratedModel
{
    public static function schemaName(): string
    {
        return __SCHEMA_NAME__;
    }
}
PHP;

    $class = str_replace('__SCHEMA_NAME__', var_export($schemaName, true), $class);
    $path = $modelDirectory . '/' . $className . '.php';

    if (file_put_contents($path, $class . "\n") === false) {
        throw new RuntimeException(sprintf('Unable to write generated schema model "%s".', $path));
    }
}

ksort($metadata);
ksort($classMap);

$resourceDirectory = dirname(__DIR__) . '/resources/generated/2026-r3';

if (file_put_contents(
    $resourceDirectory . '/schemas.json',
    json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n",
) === false) {
    throw new RuntimeException('Unable to write generated Sage schema metadata.');
}

$registry = <<<'PHP'
<?php

declare(strict_types=1);

namespace Dprmc\SageIntacctRestApi\Generated\V2026R3;

final class SageSchemaRegistry
{
    /**
     * @var array<string, class-string<\Dprmc\SageIntacctRestApi\Generated\SageGeneratedModel>>
     */
    private const SCHEMA_CLASSES = SCHEMA_CLASSES_PLACEHOLDER;

    /**
     * @return array<string, class-string<\Dprmc\SageIntacctRestApi\Generated\SageGeneratedModel>>
     */
    public static function all(): array
    {
        return self::SCHEMA_CLASSES;
    }

    /**
     * @return class-string<\Dprmc\SageIntacctRestApi\Generated\SageGeneratedModel>
     */
    public static function classFor(string $schemaName): string
    {
        if (!isset(self::SCHEMA_CLASSES[$schemaName])) {
            throw new \InvalidArgumentException(sprintf(
                'The Sage schema "%s" is not in the generated 2026 R3 registry.',
                $schemaName,
            ));
        }

        return self::SCHEMA_CLASSES[$schemaName];
    }
}
PHP;

$constantEntries = [];

foreach ($classMap as $schemaName => $className) {
    $constantEntries[] = var_export($schemaName, true) . ' => \\Dprmc\\SageIntacctRestApi\\Generated\\V2026R3\\Models\\' . $className . '::class';
}

$registry = str_replace(
    'SCHEMA_CLASSES_PLACEHOLDER',
    "[\n        " . implode(",\n        ", $constantEntries) . ",\n    ]",
    $registry,
);

$registryPath = dirname(__DIR__) . '/src/Generated/V2026R3/SageSchemaRegistry.php';

if (file_put_contents($registryPath, $registry . "\n") === false) {
    throw new RuntimeException(sprintf('Unable to write Sage schema registry "%s".', $registryPath));
}

printf(
    "Generated %d Sage schema models and registry metadata for %s.\n",
    count($classMap),
    $release,
);

/**
 * @param array<string, mixed> $schema
 * @return array<string, mixed>
 */
function normalizeSchema(array $schema): array
{
    $properties = $schema['properties'] ?? [];
    $required = $schema['required'] ?? [];

    return [
        'type' => is_string($schema['type'] ?? null) ? $schema['type'] : null,
        'format' => is_string($schema['format'] ?? null) ? $schema['format'] : null,
        'properties' => is_array($properties) ? array_keys($properties) : [],
        'required' => is_array($required) ? array_values(array_filter($required, 'is_string')) : [],
        'additional_properties' => $schema['additionalProperties'] ?? null,
        'enum' => is_array($schema['enum'] ?? null) ? array_values($schema['enum']) : [],
    ];
}

function modelClassName(string $schemaName): string
{
    $parts = preg_split('/[^A-Za-z0-9]+/', $schemaName, -1, PREG_SPLIT_NO_EMPTY);

    if ($parts === false || $parts === []) {
        throw new RuntimeException(sprintf('Schema name "%s" cannot become a PHP class name.', $schemaName));
    }

    $className = 'Sage';

    foreach ($parts as $part) {
        $className .= ucfirst(strtolower($part));
    }

    return $className;
}
