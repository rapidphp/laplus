<?php

namespace Rapid\Laplus;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void addResource(string $name, string $modelPath, string $migrationPath) Add a resource path
 * @method static void mergeResources(array $config) Merge configuration resources
 * @method static array getResources() Get all resources
 * @method static array getResource(string $name) Get a resource by name
 */
class Laplus extends Facade
{

    protected static function getFacadeAccessor()
    {
        return LaplusFactory::class;
    }

}