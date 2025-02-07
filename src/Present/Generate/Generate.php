<?php

namespace Rapid\Laplus\Present\Generate;

use Closure;
use Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Resources\Resource;

class Generate
{
    /**
     * List of resources
     *
     * @var Resource[]
     */
    protected array $resources = [];

    public static function make(): static
    {
        return new static;
    }

    public function resolve(array|Resource $resources)
    {
        $this->resources = array_merge($this->resources, is_array($resources) ? $resources : [$resources]);

        return $this;
    }

    public function forEachPath(Closure $callback): void
    {
        foreach ($this->getResourceMap() as $modelsPath => $migrationsPath) {
            $callback($modelsPath, $migrationsPath);
        }
    }

    public function forEachModels(Closure $callback): void
    {
        foreach ($this->getResourceMap() as $modelsPath => $migrationsPath) {
            foreach ($this->discoverModelsIn($modelsPath) as $model) {
                $callback($model);
            }
        };
    }

    public function forEachMigrations(Closure $callback): void
    {
        foreach ($this->getResourceMap() as $modelsPath => $migrationsPath) {
            foreach ($this->discoverMigrationsIn($migrationsPath) as $model) {
                $callback($model);
            }
        };
    }

    public function export(string|Closure $path = null): void
    {
        $generators = [];

        // Create generators
        foreach ($this->getResourceMap() as $modelsPath => $migrationsPath) {
            $generator = new MigrationGenerator();
            $generator->resolveTableFromMigration(function () use ($migrationsPath) {
                foreach ($this->discoverMigrations($migrationsPath) as $migration) {
                    $migration->up();
                }
            });

            // Pass models
            $generator->pass(iterator_to_array($this->discoverModels($modelsPath)));

            // Create folders
            @mkdir($migrationsPath, recursive: true);

            $generators[$migrationsPath] = $generator;
        }

        // Export migrations
        $exporter = new MigrationExporter();
        $files = $exporter->exportMigrationFiles($generators);
        foreach ($exporter->exportMigrationStubs($files) as $name => $stub) {
            $migrationsPath = value($path) ?? $files->files[$name]->tag;
            if (file_exists("$migrationsPath/$name.php")) {
                throw new \RuntimeException("Migration [$migrationsPath/$name.php] is already exists");
            }

            file_put_contents("$migrationsPath/$name.php", $stub);
        }
    }

    public function getResourceMap(): array
    {
        return Arr::mapWithKeys($this->resources, fn(Resource $resource) => $resource->resolve());
    }

    public function discoverModelsIn(string $path): Generator
    {
        if (file_exists($path)) {
            foreach (scandir($path) as $sub) {
                if ($sub == '.' || $sub == '..')
                    continue;

                $subPath = $path . '/' . $sub;

                if (is_dir($subPath)) {
                    foreach ($this->discoverModelsIn($subPath) as $model) {
                        yield $model;
                    }
                } else {
                    if (str_ends_with($sub, '.php')) {
                        $contents = @file_get_contents($subPath);
                        if (
                            preg_match('/namespace\s+(.*?)\s*;/', $contents, $namespaceMatch) &&
                            preg_match('/class\s+(.*?)[\s\n\r{]/', $contents, $classMatch)
                        ) {
                            $class = $namespaceMatch[1] . "\\" . $classMatch[1];
                            if (
                                class_exists($class) &&
                                is_a($class, Model::class, true) &&
                                in_array(HasPresent::class, class_uses_recursive($class))
                            ) {
                                yield $class;
                            }
                        }
                    }
                }
            }
        }
    }

    public function discoverMigrationsIn(string $path): Generator
    {
        if (!file_exists($path)) {
            @mkdir($path, recursive: true);
        }

        foreach (scandir($path) as $sub) {
            if ($sub == '.' || $sub == '..')
                continue;

            $subPath = $path . '/' . $sub;

            if (is_dir($subPath)) {
                foreach ($this->discoverMigrationsIn($subPath) as $migration) {
                    yield $migration;
                }
            } else {
                if (str_ends_with($sub, '.php')) {
                    $value = include $subPath;
                    if ($value instanceof Migration) {
                        yield $value;
                    }
                }
            }
        }
    }

}