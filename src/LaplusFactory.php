<?php

namespace Rapid\Laplus;

use Rapid\Laplus\Resources\FixedResource;
use Rapid\Laplus\Resources\ModularResource;
use Rapid\Laplus\Resources\Resource;

class LaplusFactory
{

    /**
     * @var Resource[]
     */
    public array $resources = [];

    /**
     * Add a resource path
     *
     * @param string   $name
     * @param Resource $resource
     * @return void
     */
    public function addResource(string $name, Resource $resource)
    {
        $this->resources[$name] = $resource;
    }

    /**
     * Merge configuration resources
     *
     * @param Resource[] $resources
     * @return void
     */
    public function mergeResources(array $resources)
    {
        foreach ($resources as $name => $resource)
        {
            $this->addResource($name, $resource);
        }
    }

    /**
     * @param array $config
     * @return void
     */
    public function loadConfig(array $config)
    {
        foreach ($config as $name => $conf)
        {
            $type = $conf['type'] ?? 'default';

            $type = match ($type)
            {
                'default' => FixedResource::class,
                'modular' => ModularResource::class,
                default => $type,
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
    public function getResource(string $name) : ?Resource
    {
        return $this->resources[$name] ?? null;
    }

    /**
     * Get all resources
     *
     * @return Resource[]
     */
    public function getResources() : array
    {
        return $this->resources;
    }

}