<?php

namespace Rapid\Laplus\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class LaplusSnapshotCommand extends LaplusBaseResourceCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laplus:snapshot {--migrations= : Migrations path} {--models= : Models path} {--name= : Laplus name} {--regenerate : Regenerate migrations}';
    protected $aliases = ['snapshot+'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take new snapshot';

    public function handle()
    {
        if ($this->option('regenerate'))
        {

        }

        return parent::handle();
    }

    public function generate(string $modelPath, string $migrationPath)
    {
        // Create folders
        $this->makeReadyToWrite($migrationPath);

        file_put_contents(
            $migrationPath . '/' . $this->snapshotFileName,
            $this->snapshotFileContent,
        );

        $this->output->success("New snapshot {$this->snapshotFileName} created!");

        return 0;
    }

    protected string $snapshotFileName;
    protected string $snapshotFileContent;

    public function preGenerate(array $map)
    {
        if (!$map) return;

        $suggestedFileName = [];
        foreach ($map as $modelsPath => $migrationsPath)
        {
            $bestName = date('Y_m_d_His');
            $lastExistsFile = @last(@scandir($migrationsPath) ?: []);
            if (
                $lastExistsFile &&
                is_file($migrationsPath . '/' . $lastExistsFile) &&
                preg_match('/^(\d{4})_(\d{2})_(\d{2})_(\d{6})/', $lastExistsFile, $matches)
            )
            {
                $matches[4]++;
                $matches[4] = @str_repeat('0', 6 - strlen($matches[4]));
                $bestName = $matches[1] . '_' . $matches[2] . '_' . $matches[3] . '_' . $matches[4];
            }

            $suggestedFileName[] = $bestName;
        }

        sort($suggestedFileName);
        $this->snapshotFileName = end($suggestedFileName) . '.snapshot';
        $this->snapshotFileContent = 'Laplus Snapshot';
    }

}