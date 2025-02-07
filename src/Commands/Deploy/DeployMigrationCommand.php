<?php

namespace Rapid\Laplus\Commands\Deploy;

use Illuminate\Console\Command;
use Rapid\Laplus\Laplus;
use Rapid\Laplus\Present\Generate\Generate;

class DeployMigrationCommand extends Command
{

    protected $signature = 'deploy:migration';
    protected $description = 'Generate migration file for deploy';

    public function handle()
    {
        Generate::make()
            ->resolve(Laplus::getResources())
            ->deleteDevMigrations()
            ->export();

        $this->components->info('Migration created successfully!');
    }

}