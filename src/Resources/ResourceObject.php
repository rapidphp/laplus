<?php

namespace Rapid\Laplus\Resources;

use Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Travel\Travel;

final readonly class ResourceObject
{
    public function __construct(
        public string $modelsPath,
        public string $migrationsPath,
        public string $devMigrationsPath,
        public string $travelsPath,
    )
    {
    }

    /**
     * Discover list of model classes
     *
     * @return string[]
     */
    public function discoverModels(): array
    {
        return iterator_to_array($this->discoverModelsIn($this->modelsPath));
    }

    /**
     * Discover list of migrations
     *
     * @param bool $dev
     * @return Migration[]
     */
    public function discoverMigrations(bool $dev = false): array
    {
        if ($dev) {
            $migrations = [
                ...iterator_to_array($this->discoverMigrationsIn($this->migrationsPath)),
                ...iterator_to_array($this->discoverMigrationsIn($this->devMigrationsPath)),
            ];
            $migrationsKey = array_keys($migrations);
            sort($migrationsKey);

            $newMigrations = [];
            foreach ($migrationsKey as $migration) {
                $newMigrations[$migration] = $migrations[$migration];
            }

            return $newMigrations;
        }

        return iterator_to_array($this->discoverMigrationsIn($this->migrationsPath));
    }

    /**
     * Discover list of migrations in dev path
     *
     * @return string[]
     */
    public function discoverDevMigrationsPath(): array
    {
        return iterator_to_array($this->discoverMigrationsPathIn($this->devMigrationsPath));
    }

    /**
     * Discover list of travels
     *
     * @return Travel[]
     */
    public function discoverTravels(): array
    {
        if (!is_dir($this->travelsPath)) {
            return [];
        }

        $travelsPath = str_replace('\\', '/', $this->travelsPath);
        $basePath = str_replace('\\', '/', base_path());

        if (!str_starts_with($travelsPath, $basePath)) {
            throw new \Exception("Travel path [$travelsPath] must be in the root directory");
        }

        $relativePath = ltrim(substr($travelsPath, strlen($basePath)), '/');

        $all = [];
        foreach (scandir($this->travelsPath) as $subPath) {
            if (!str_ends_with($subPath, '.php')) {
                continue;
            }

            $all["$relativePath/$subPath"] = $this->requireTravelFile("{$this->travelsPath}/$subPath");
        }

        return $all;
    }

    protected function discoverModelsIn(string $path): Generator
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
                            preg_match('/class\s+(.*?)[\s\n\r{]/', $contents, $classMatch) &&
                            !$this->shouldIgnoreModel($class = $namespaceMatch[1] . "\\" . $classMatch[1])
                        ) {
                            yield $class;
                        }
                    }
                }
            }
        }
    }

    protected function shouldIgnoreModel(string $class): bool
    {
        return !class_exists($class) ||
            !is_a($class, Model::class, true) ||
            !in_array(HasPresent::class, class_uses_recursive($class)) ||
            (new \ReflectionClass($class))->isAbstract() ||
            (new $class)->shouldIgnore();
    }

    protected function discoverMigrationsIn(string $path): Generator
    {
        foreach ($this->discoverMigrationsPathIn($path) as $subPath) {
            $value = include $subPath;
            if ($value instanceof Migration) {
                yield pathinfo($subPath, PATHINFO_BASENAME) => $value;
            }
        }
    }

    protected function discoverMigrationsPathIn(string $path): Generator
    {
        if (!file_exists($path)) {
            @mkdir($path, recursive: true);
        }

        foreach (scandir($path) as $sub) {
            if ($sub == '.' || $sub == '..') {
                continue;
            }

            $subPath = $path . '/' . $sub;

            if (is_dir($subPath)) {
                foreach ($this->discoverMigrationsPathIn($subPath) as $migration) {
                    yield $migration;
                }
            } else {
                if (str_ends_with($sub, '.php')) {
                    yield $subPath;
                }
            }
        }
    }

    protected function requireTravelFile(string $__path): Travel
    {
        return require $__path;
    }
}
