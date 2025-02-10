<?php

namespace Rapid\Laplus\Commands\Dev;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DevMigrateCommand extends Command
{

    protected $signature = 'dev:migrate {--fresh : Fresh the previous migrations} {--guide : Generate the guides}';
    protected $description = 'Generate and run migrations in dev mode';

    public function handle()
    {
        if ($this->option('guide')) {
            Artisan::call('dev:guide');
        }

        Artisan::call('dev:migration', ['--fresh' => $this->option('fresh')], $this->output);
        Artisan::call('migrate' . ($this->option('fresh') ? ':fresh' : ''), [], $this->output);
    }

}