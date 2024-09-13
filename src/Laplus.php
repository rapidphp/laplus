<?php

namespace Rapid\Laplus;

use Illuminate\Support\Facades\Facade;
use Rapid\Laplus\Resources\Resource;

/**
 * @method static void addResource(string $name, Resource $resource) Add a resource path
 * @method static void mergeResources(array $resources) Merge configuration resources
 * @method static void loadConfig(array $config) Merge configuration resources
 * @method static Resource[] getResources() Get all resources
 * @method static Resource|null getResource(string $name) Get a resource by name
 */
class Laplus extends Facade
{

    protected static function getFacadeAccessor()
    {
        return LaplusFactory::class;
    }

}