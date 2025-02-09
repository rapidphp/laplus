<?php

namespace Rapid\Laplus\Resources;

use Illuminate\Support\Arr;

readonly class ModularResource extends Resource
{

    public function __construct(
        public string $modules,
        public string $models,
        public string $migrations,
        public string $devMigrations,
        public string $travels,
    )
    {
    }

    public static function fromConfig(string $name, array $config): Resource
    {
        if (!Arr::has($config, ['modules', 'models', 'migrations', 'dev_migrations', 'travels'])) {
            throw new \InvalidArgumentException(
                "Laplus config [$name] should contains [modules], [models], [migrations], [dev_migrations] and [travels] values"
            );
        }

        return new static($config['modules'], $config['models'], $config['migrations'], $config['dev_migrations'], $config['travels']);
    }

    public function resolve(): array
    {
        $all = [];

        foreach (glob($this->modules . '/*') as $module) {
            if (is_dir($module)) {
                $all[] = new ResourceObject(
                    modelsPath: "{$module}/{$this->models}",
                    migrationsPath: "{$module}/{$this->migrations}",
                    devPath: "{$module}/{$this->devMigrations}",
                    travelsPath: "{$module}/{$this->travels}",
                );
            }
        }

        return $all;
    }

}