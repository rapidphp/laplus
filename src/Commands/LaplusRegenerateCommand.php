<?php

namespace Rapid\Laplus\Commands;

use Illuminate\Support\Facades\File;
use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('laplus:regenerate')]
class LaplusRegenerateCommand extends LaplusBaseResourceCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laplus:regenerate {--migrations= : Migrations path} {--models= : Models path} {--name= : Laplus name}';

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
        $generator = new MigrationGenerator();

        // Pass models & generate new migrations
        $generator->pass(iterator_to_array($this->discoverModels($modelPath)));
        $files = $generator->generateMigrationFiles();
        $stubs = $generator->generateMigrationStubs($files);

        // Delete old migrations
        foreach (glob($migrationPath . '/*') as $path)
        {
            if (!is_dir($path))
            {
                @unlink($path);
            }
        }

        // Save new migrations
        foreach ($stubs as $name => $stub)
        {
            file_put_contents("$migrationPath/$name.php", $stub);
        }

        $this->output->success("Migrations generated successfully!");

        return 0;
    }

}