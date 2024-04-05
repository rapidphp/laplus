<?php

namespace Rapid\Laplus;

class LaplusFactory
{

    public array $resources = [];

    /**
     * Add a resource path
     *
     * @param string $name
     * @param string $modelPath
     * @param string $migrationPath
     * @return void
     */
    public function addResource(string $name, string $modelPath, string $migrationPath)
    {
        $this->resources[$name] = [
            'models' => $modelPath,
            'migrations' => $migrationPath,
        ];
    }

    /**
     * Merge configuration resources
     *
     * @param array $config
     * @return void
     */
    public function mergeResources(array $config)
    {
        $this->resources = [...$this->resources, ...$config];
    }

    /**
     * Get a resource by name
     *
     * @param string $name
     * @return array
     */
    public function getResource(string $name)
    {
        return $this->resources[$name] ?? null;
    }

    /**
     * Get all resources
     *
     * @return array
     */
    public function getResources()
    {
        return $this->resources;
    }

}