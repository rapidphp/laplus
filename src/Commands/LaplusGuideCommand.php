<?php

namespace Rapid\Laplus\Commands;

use Illuminate\Console\Command;
use Rapid\Laplus\Guide\Guides\DocblockGuide;
use Rapid\Laplus\Guide\Guides\MixinGuide;
use Rapid\Laplus\Guide\ModelAuthor;

class LaplusGuideCommand extends LaplusBaseResourceCommand
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laplus:guide {--migrations= : Migrations path} {--models= : Models path} {--name= : Laplus name}';
    protected $aliases   = ['guide+'];

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

        $guide = match (config('laplus.guide.type'))
        {
            'docblock' => new DocblockGuide(),
            'mixin'    => new MixinGuide(config('laplus.guide.mixin.path'), config('laplus.guide.mixin.namespace')),
            default    => throw new \Exception('Guide type not supported'),
        };

        $authors = [];
        foreach ($all as $modelName)
        {
            $authors[] = new ModelAuthor($guide, $modelName);
        }

        $guide->run($authors);

        $this->components->info("Guide docblock generated successfully!");
    }
}