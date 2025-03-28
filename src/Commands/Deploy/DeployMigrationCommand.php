<?php

namespace Rapid\Laplus\Commands\Deploy;

use Illuminate\Console\Command;
use Rapid\Laplus\Laplus;
use Rapid\Laplus\Present\Generate\Generate;
use Rapid\Laplus\Resources\PackageResource;
use Rapid\Laplus\Resources\Resource;

class DeployMigrationCommand extends Command
{

    protected $signature = 'deploy:migration {--package= : The specific package name}';
    protected $description = 'Generate migration file for deploy';

    public function handle()
    {
        $resources = Laplus::getResources();

        if ($package = $this->option('package')) {
            $resources = array_filter($resources, function (Resource $resource) use ($package) {
                return $resource instanceof PackageResource && $resource->packageName == $package;
            });

            if (!$resources) {
                $this->components->error("No package found for {$package}");
                return false;
            }
        }

        Generate::make()
            ->resolve($resources)
            ->deleteDevMigrations()
            ->export();

        $this->components->info('Migration created successfully!');
    }

}