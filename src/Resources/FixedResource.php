<?php

namespace Rapid\Laplus\Resources;

use Illuminate\Support\Arr;
use InvalidArgumentException;

readonly class FixedResource extends Resource
{

    public function __construct(
        public string $models,
        public string $migrations,
        public string $devMigrations,
        public string $travelsMigrations,
    )
    {
    }

    public static function fromConfig(string $name, array $config): Resource
    {
        if (!Arr::has($config, ['models', 'migrations', 'dev_migrations', 'travels'])) {
            throw new InvalidArgumentException(
                "Laplus config [$name] should contains [models], [migrations], [dev_migrations] and [travels] values"
            );
        }

        return new static($config['models'], $config['migrations'], $config['dev_migrations'], $config['travels']);
    }

    public function resolve(): array
    {
        return [
            new ResourceObject($this->models, $this->migrations, $this->devMigrations, $this->travelsMigrations),
        ];
    }

}