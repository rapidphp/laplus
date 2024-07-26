<?php

namespace Rapid\Laplus\Commands;

class LaplusRegenerateCommand extends LaplusGenerateCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laplus:regenerate {--migrations= : Migrations path} {--models= : Models path} {--name= : Laplus name}';
    protected $aliases = ['regenerate+'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old migrations and Re-generate the migrations using presents';

    public function handle()
    {
        if (!$this->output->confirm("All migrations will be deleted! Are you sure?", false))
        {
            return 1;
        }

        return parent::handle();
    }

    public function generate(string $modelPath, string $migrationPath)
    {
        // Delete old migrations
        foreach (glob($migrationPath . '/*') as $path)
        {
            if (!is_dir($path) && !str_contains($path, '0001_01_01'))
            {
                @unlink($path);
            }
        }

        return parent::generate($modelPath, $migrationPath);
    }

}