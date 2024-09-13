<?php

namespace Rapid\Laplus\Resources;

use Illuminate\Support\Arr;

readonly class ModularResource extends Resource
{

    public function __construct(
        public string $modules,
        public string $models,
        public string $migrations,
    )
    {
    }

    public static function fromConfig(string $name, array $config) : Resource
    {
        if (!Arr::has($config, ['modules', 'models', 'migrations']))
        {
            throw new \InvalidArgumentException("Laplus config [$name] should contains [modules], [models] and [migrations] values");
        }

        return new static($config['modules'], $config['models'], $config['migrations']);
    }

    public function resolve() : array
    {
        $all = [];

        foreach (glob($this->modules . '/*') as $module)
        {
            $all[$module . '/' . $this->models] = $module . '/' . $this->migrations;
        }

        return $all;
    }

}