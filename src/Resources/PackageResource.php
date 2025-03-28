<?php

namespace Rapid\Laplus\Resources;

readonly class PackageResource extends FixedResource
{
    public function __construct(
        public string $packageName,
        string        $models,
        string        $migrations,
        string        $devMigrations,
        string        $travelsPath,
    )
    {
        parent::__construct($models, $migrations, $devMigrations, $travelsPath);
    }

    public function shouldGenerate(): bool
    {
        $path = str_replace('\\', '/', $this->migrations);

        if (str_contains($path, '/vendor/')) {
            $exploded = explode('/vendor/', $path);
            $path = array_shift($exploded);

            do {
                if (file_exists("$path/composer.json")) {
                    return false;
                }
            } while ($exploded && $path .= '/vendor/' . array_shift($exploded));
        }

        return parent::shouldGenerate();
    }

    public function shouldAddGitIgnoreForDev(): bool
    {
        return false;
    }
}