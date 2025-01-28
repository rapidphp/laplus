<?php

namespace Rapid\Laplus\Resources;

abstract readonly class Resource
{

    /**
     * Create instance from config
     *
     * @param string $name
     * @param array $config
     * @return static
     */
    public static abstract function fromConfig(string $name, array $config): Resource;

    /**
     * Get the model and migration paths.
     *
     * @return array<string, string>
     */
    public abstract function resolve(): array;

}