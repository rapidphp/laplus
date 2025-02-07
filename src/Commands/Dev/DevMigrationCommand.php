<?php

namespace Rapid\Laplus\Commands\Dev;

use Illuminate\Console\Command;
use Rapid\Laplus\Laplus;
use Rapid\Laplus\Present\Generate\Generate;

class DevMigrationCommand extends Command
{

    protected $signature = 'dev:migration {--fresh : Fresh the previous migrations}';
    protected $description = 'Generate migration file in dev mode';

    public function handle()
    {
        Generate::make()
            ->resolve(Laplus::getResources())
            ->dev()
            ->addDevGitIgnores()
            ->when($this->option('fresh'))->deleteDevMigrations()
            ->export();

        $this->components->info('Migration created successfully!');
    }

}