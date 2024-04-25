<?php

namespace Rapid\Laplus\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('laplus:migrate')]
class LaplusMigrateCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laplus:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate models and migrate database using presents';

    public function handle()
    {
        Artisan::call('laplus:generate');
        Artisan::call('migrate');
    }

}