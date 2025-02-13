<?php

namespace Rapid\Laplus\Commands\Dev;

use Illuminate\Console\Command;
use Rapid\Laplus\Laplus;
use Rapid\Laplus\Present\Generate\Generate;

class DevMigrationCommand extends Command
{

    protected $signature = 'dev:migration {--fresh : Fresh the previous migrations} {--clear : Clear the previous migrations}';
    protected $description = 'Generate migration file in dev mode';

    public function handle()
    {
        if ($this->option('clear')) {
            Generate::make()
                ->resolve(Laplus::getResources())
                ->deleteDevMigrations();

            $this->components->info('Dev migrations successfully cleared!');
            return;
        }

        Generate::make()
            ->resolve(Laplus::getResources())
            ->dev()
            ->addDevGitIgnores()
            ->when($this->option('fresh'))->deleteDevMigrations()
            ->export();

        $this->components->info('Migration created successfully!');
    }

}