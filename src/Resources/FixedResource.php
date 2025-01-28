<?php

namespace Rapid\Laplus\Resources;

use Illuminate\Support\Arr;

readonly class FixedResource extends Resource
{

    public function __construct(
        public string $models,
        public string $migrations,
    )
    {
    }

    public static function fromConfig(string $name, array $config): Resource
    {
        if (!Arr::has($config, ['models', 'migrations'])) {
            throw new \InvalidArgumentException("Laplus config [$name] should contains [models] and [migrations] values");
        }

        return new static($config['models'], $config['migrations']);
    }

    public function resolve(): array
    {
        return [$this->models => $this->migrations];
    }

}