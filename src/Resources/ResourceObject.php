<?php

namespace Rapid\Laplus\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Rapid\Laplus\Present\HasPresent;
use Generator;

final readonly class ResourceObject
{
    public function __construct(
        public string  $modelsPath,
        public string  $migrationsPath,
        public ?string $devPath = null,
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
                ...iterator_to_array($this->discoverMigrationsIn($this->devPath)),
            ];
            asort($migrations);
        } else {
            $migrations = iterator_to_array($this->discoverMigrationsIn($this->migrationsPath));
        }

        return $migrations;
    }

    /**
     * Discover list of migrations in dev path
     *
     * @return string[]
     */
    public function discoverDevMigrationsPath(): array
    {
        return iterator_to_array($this->discoverMigrationsPathIn($this->devPath));
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

    protected function discoverMigrationsIn(string $path): Generator
    {
        foreach ($this->discoverMigrationsPathIn($path) as $subPath) {
            $value = include $subPath;
            if ($value instanceof Migration) {
                yield pathinfo($path, PATHINFO_BASENAME) => $value;
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

}