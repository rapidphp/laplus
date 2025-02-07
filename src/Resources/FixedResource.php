<?php

namespace Rapid\Laplus\Resources;

use Illuminate\Support\Arr;

readonly class FixedResource extends Resource
{

    public function __construct(
        public string $models,
        public string $migrations,
        public string $devPath,
    )
    {
    }

    public static function fromConfig(string $name, array $config): Resource
    {
        if (!Arr::has($config, ['models', 'migrations', 'dev_migrations'])) {
            throw new \InvalidArgumentException("Laplus config [$name] should contains [models], [migrations] and [dev_migrations] values");
        }

        return new static($config['models'], $config['migrations'], $config['dev_migrations']);
    }

    public function resolve(): array
    {
        return [
            new ResourceObject($this->models, $this->migrations, $this->devPath),
        ];
    }

}