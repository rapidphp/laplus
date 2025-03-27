<?php

namespace Rapid\Laplus\Resources;

readonly class PackageResource extends FixedResource
{
    public function __construct(
        public string $packageName,
        string $models,
        string $travelsPath,
    )
    {
        $basePath = config('laplus.vendor.migrations') ?? base_path('database/migrations/vendor');

        parent::__construct(
            models: $models,
            migrations: "$basePath/$packageName",
            devPath: "$basePath/$packageName/dev_generated",
            travelsPath: $travelsPath,
        );
    }
}