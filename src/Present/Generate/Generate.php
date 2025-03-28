<?php

namespace Rapid\Laplus\Present\Generate;

use Closure;
use Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Conditionable;
use Rapid\Laplus\Editors\GitIgnoreEditor;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Resources\Resource;
use Rapid\Laplus\Resources\ResourceObject;

class Generate
{
    use Conditionable;

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

        $this->resources = array_filter($this->resources, static function (Resource $resource) {
            return $resource->shouldGenerate();
        });

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
                if (!$resource->shouldAddGitIgnoreForDev()) {
                    continue;
                }

                GitIgnoreEditor::make($resourceObject->devMigrationsPath)
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

    public function forEach(Closure $callback): void
    {
        foreach ($this->resources as $resource) {
            foreach ($resource->resolve() as $resourceObject) {
                $callback($resourceObject);
            }
        }
    }

    public function forEachModels(Closure $callback): void
    {
        $this->forEach(function (ResourceObject $resource) use ($callback) {
            foreach ($resource->discoverModels() as $model) {
                $callback($model);
            }
        });
    }

    public function export(): void
    {
        $generators = [];

        // Create generators
        $this->forEach(function (ResourceObject $resource) use (&$generators) {
            $generator = new MigrationGenerator();

            $generator->resolveTableFromMigration(function () use ($resource) {
                foreach ($resource->discoverMigrations($this->dev) as $migration) {
                    $migration->up();
                }
            });

            $generator->discoverTravels($resource->discoverTravels());

            $generator->pass($resource->discoverModels());

            // Create folders
            $output = $this->dev ? $resource->devMigrationsPath : $resource->migrationsPath;
            @mkdir($output, recursive: true);

            $generators[$output] = $generator;
        });

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