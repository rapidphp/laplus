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

    public bool $dev = false;

    public static function make(): static
    {
        return new static;
    }

    public function resolve(array|Resource $resources)
    {
        $this->resources = array_merge($this->resources, is_array($resources) ? $resources : [$resources]);

        return $this;
    }

    public function dev()
    {
        $this->dev = true;

        return $this;
    }

    public function addDevGitIgnores()
    {
        foreach ($this->resources as $resource) {
            foreach ($resource->resolve() as $resourceObject) {
                GitIgnoreEditor::make($resourceObject->devPath)
                    ->add('*')
                    ->add('!.gitignore')
                    ->save();
            }
        }

        return $this;
    }

    public function deleteDevMigrations()
    {
        foreach ($this->resources as $resource) {
            foreach ($resource->resolve() as $resourceObject) {
                foreach ($resourceObject->discoverDevMigrationsPath() as $path) {
                    unlink($path);
                }
            }
        }

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

    public function export(): void
    {
        $generators = [];

        // Create generators
        foreach ($this->resources as $resource) {
            foreach ($resource->resolve() as $resourceObject) {
                $generator = new MigrationGenerator();
                $generator->resolveTableFromMigration(function () use ($resourceObject) {
                    foreach ($resourceObject->discoverMigrations($this->dev) as $migration) {
                        $migration->up();
                    }
                });

                // Pass models
                $generator->pass($resourceObject->discoverModels());

                // Create folders
                $output = $this->dev ? $resourceObject->devPath : $resourceObject->migrationsPath;
                @mkdir($output, recursive: true);

                $generators[$output] = $generator;
            }
        }

        // Export migrations
        $exporter = new MigrationExporter();
        $files = $exporter->exportMigrationFiles($generators);
        foreach ($exporter->exportMigrationStubs($files) as $name => $stub) {
            $migrationsPath = $files->files[$name]->tag;
            if (file_exists("$migrationsPath/$name.php")) {
                throw new \RuntimeException("Migration [$migrationsPath/$name.php] is already exists");
            }

            file_put_contents("$migrationsPath/$name.php", $stub);
        }
    }

}