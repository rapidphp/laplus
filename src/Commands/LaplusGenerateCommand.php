<?php

namespace Rapid\Laplus\Commands;

use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('laplus:generate')]
class LaplusGenerateCommand extends LaplusBaseResourceCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laplus:generate {--migrations= : Migrations path} {--models= : Models path} {--name= : Laplus name}';
    protected $aliases = ['generate+'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new migrations using presents';

    public function generate(string $modelPath, string $migrationPath)
    {
        // Load old migrations
        $generator = new MigrationGenerator();
        $generator->resolveTableFromMigration(function () use($migrationPath)
        {
            foreach ($this->discoverMigrations($migrationPath) as $migration)
            {
                $migration->up();
            }
        });

        // Pass models
        $generator->pass(iterator_to_array($this->discoverModels($modelPath)));

        // Create folders
        $this->makeReadyToWrite($migrationPath);

        // Generate different & migrations
        $files = $generator->generateMigrationFiles();
        foreach ($generator->generateMigrationStubs($files) as $name => $stub)
        {
            if (file_exists("$migrationPath/$name.php"))
            {
                $this->error("Migration [$migrationPath/$name.php] is already exists");
                return 1;
            }

            file_put_contents("$migrationPath/$name.php", $stub);
        }

        $this->output->success("Migrations generated successfully!");

        return 0;
    }

}