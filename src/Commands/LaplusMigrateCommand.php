<?php

namespace Rapid\Laplus\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class LaplusMigrateCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laplus:migrate';
    protected $aliases = ['migrate+'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate models and migrate database using presents';

    public function handle()
    {
        Artisan::call('laplus:generate', outputBuffer: $this->output);
        Artisan::call('migrate', outputBuffer: $this->output);
    }

}