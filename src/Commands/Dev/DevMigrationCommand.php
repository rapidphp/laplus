<?php

namespace Rapid\Laplus\Commands\Dev;

use Illuminate\Console\Command;
use Rapid\Laplus\Laplus;
use Rapid\Laplus\Present\Generate\Generate;
use Rapid\Laplus\Resources\PackageResource;
use Rapid\Laplus\Resources\Resource;

class DevMigrationCommand extends Command
{

    protected $signature = 'dev:migration {--fresh : Fresh the previous migrations} {--clear : Clear the previous migrations} {--package= : The specific package name}';
    protected $description = 'Generate migration file in dev mode';

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

        if ($this->option('clear')) {
            Generate::make()
                ->resolve($resources)
                ->deleteDevMigrations();

            $this->components->info('Dev migrations successfully cleared!');
            return true;
        }

        Generate::make()
            ->resolve($resources)
            ->dev()
            ->addDevGitIgnores()
            ->when($this->option('fresh'))->deleteDevMigrations()
            ->export();

        $this->components->info('Migration created successfully!');
        return true;
    }

}