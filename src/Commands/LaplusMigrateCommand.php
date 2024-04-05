<?php

namespace Rapid\Laplus\Commands;

use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('laplus:migrate')]
class LaplusMigrateCommand extends LaplusBaseResourceCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laplus:migrate {--migrations= : Migrations path} {--models= : Models path} {--name= : Laplus name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate database using presents';

    public function generate(string $modelPath, string $migrationPath)
    {
        $this->error("Coming soon...");
        die;

        $generator = new MigrationGenerator();
        $generator->includeDropTables = false;
        $generator->resolveTableFromDatabase();

        $generator->pass(iterator_to_array($this->discoverModels($modelPath)));
        $files = $generator->generateMigrationFiles();
        dd($files);
        foreach ($generator->generateMigrationStubs($files) as $name => $stub)
        {
            if (file_exists("$migrationPath/$name.php"))
            {
                $this->error("Migration [$migrationPath/$name.php] is already exists");
                return 1;
            }

            // echo "$migrationPath/$name.php\n";
            file_put_contents("$migrationPath/$name.php", $stub);
        }

        $this->output->success("Migrations generated successfully!");

        return 0;
    }

}