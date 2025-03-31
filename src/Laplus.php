<?php

namespace Rapid\Laplus;

use Illuminate\Support\Facades\Facade;
use Rapid\Laplus\Resources\PackageResource;
use Rapid\Laplus\Resources\Resource;
use Rapid\Laplus\Resources\SharedPackageResource;

/**
 * @method static void addResource(string $name, Resource $resource) Add a resource path
 * @method static void mergeResources(array $resources) Merge configuration resources
 * @method static void loadConfig(array $config) Merge configuration resources
 * @method static Resource[] getResources() Get all resources
 * @method static Resource|null getResource(string $name) Get a resource by name
 * @method static void registerPackageResource(SharedPackageResource|PackageResource $resource) Register a package resource
 */
class Laplus extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LaplusFactory::class;
    }
}