<?php

namespace Rapid\Laplus\Commands;

use Illuminate\Console\Command;

class LaplusDocblockCommand extends LaplusBaseResourceCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laplus:docblock';
    protected $aliases = ['docblock+'];

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate automatically docblock for IDE';


    public function generate(string $modelPath, string $migrationPath)
    {
        $contents = file_get_contents($modelPath, length: 5000);
        if (
            preg_match('/[\n\s]namespace\s+([a-zA-Z0-9_\/]+)\s*;/', $contents, $namespace) &&
            preg_match('/[\n\s]class\s+([a-zA-Z0-9_\/]+)/', $contents, $class)
        )
        {
            if (class_exists($className = $namespace[1] . "\\" . $class[1]))
            {
                return $className;
            }
        }

        return null;
    }

    public function export(array $all)
    {
        $all = array_values(array_filter($all));

        dd($all);
    }
}