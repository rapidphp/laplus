<?php

namespace Rapid\Laplus\Commands;

use Illuminate\Console\Command;
use Rapid\Laplus\Guide\ModelGuide;

class LaplusDocblockCommand extends LaplusBaseResourceCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laplus:docblock {--migrations= : Migrations path} {--models= : Models path} {--name= : Laplus name}';
    protected $aliases = ['docblock+'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate automatically docblock for IDE';


    public function generate(string $modelPath, string $migrationPath)
    {
        return iterator_to_array($this->discoverModels($modelPath));
    }

    public function export(array $all)
    {
        $all = collect($all)->values()->filter()->flatten();

        foreach ($all as $modelName)
        {
            $guide = new ModelGuide($modelName);

            $guide->write();
        }
    }
}