<?php

namespace Rapid\Laplus\Resources;

readonly class SharedPackageResource extends FixedResource
{
    public function __construct(
        public string $packageName,
        string        $models,
        string        $travelsPath,
    )
    {
        $basePath = config('laplus.vendor.migrations') ?? database_path('migrations/vendor');

        parent::__construct(
            models: $models,
            migrations: "$basePath/$packageName",
            devMigrations: "$basePath/$packageName/dev_generated",
            travelsMigrations: $travelsPath,
        );
    }
}