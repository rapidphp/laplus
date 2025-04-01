<?php

namespace Rapid\Laplus;

use Illuminate\Database\Migrations\Migrator;
use Rapid\Laplus\Resources\FixedResource;
use Rapid\Laplus\Resources\ModularResource;
use Rapid\Laplus\Resources\PackageResource;
use Rapid\Laplus\Resources\Resource;
use Rapid\Laplus\Resources\SharedPackageResource;

class LaplusFactory
{

    /**
     * @var Resource[]
     */
    public array $resources = [];

    /**
     * Merge configuration resources
     *
     * @param Resource[] $resources
     * @return void
     */
    public function mergeResources(array $resources): void
    {
        foreach ($resources as $name => $resource) {
            $this->addResource($name, $resource);
        }
    }

    /**
     * Add a resource path
     *
     * @param string $name
     * @param Resource $resource
     * @return void
     */
    public function addResource(string $name, Resource $resource): void
    {
        $this->resources[$name] = $resource;
    }

    /**
     * Register a package resource
     *
     * @param SharedPackageResource|PackageResource $resource
     * @return void
     */
    public function registerPackageResource(SharedPackageResource|PackageResource $resource): void
    {
        $this->addResource('vendor/' . $resource->packageName, $resource);

        $callback = function (Migrator $migrator) use ($resource) {
            foreach ($resource->resolve() as $res) {
                $migrator->path($res->migrationsPath);
                if ($resource instanceof SharedPackageResource) {
                    $migrator->path($res->devMigrationsPath);
                }
            }
        };

        $app = app();
        $app->afterResolving('migrator', $callback);

        if ($app->resolved('migrator')) {
            $callback($app->make('migrator'));
        }
    }

    /**
     * @param array $config
     * @return void
     */
    public function loadConfig(array $config)
    {
        foreach ($config as $name => $conf) {
            $type = $conf['type'] ?? 'default';

            $type = match ($type) {
                'default' => FixedResource::class,
                'modular' => ModularResource::class,
                default   => $type,
            };

            $this->addResource($name, $type::fromConfig($name, $conf));
        }
    }

    /**
     * Get a resource by name
     *
     * @param string $name
     * @return ?Resource
     */
    public function getResource(string $name): ?Resource
    {
        return $this->resources[$name] ?? null;
    }

    /**
     * Get all resources
     *
     * @return Resource[]
     */
    public function getResources(): array
    {
        return $this->resources;
    }

}