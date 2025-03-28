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
    abstract public static function fromConfig(string $name, array $config): Resource;

    /**
     * Get the resources
     *
     * @return ResourceObject[]
     */
    abstract public function resolve(): array;

    /**
     * Determines the resource should be generated
     *
     * @return bool
     */
    public function shouldGenerate(): bool
    {
        return true;
    }

    /**
     * Determines the resource should add the git ignore file for dev generated files
     *
     * @return bool
     */
    public function shouldAddGitIgnoreForDev(): bool
    {
        return true;
    }

}