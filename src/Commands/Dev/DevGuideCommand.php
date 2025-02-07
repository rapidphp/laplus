<?php

namespace Rapid\Laplus\Commands\Dev;

use Illuminate\Console\Command;
use Rapid\Laplus\Guide\Guides\DocblockGuide;
use Rapid\Laplus\Guide\Guides\MixinGuide;
use Rapid\Laplus\Guide\ModelAuthor;
use Rapid\Laplus\Laplus;
use Rapid\Laplus\Present\Generate\Generate;

class DevGuideCommand extends Command
{

    protected $signature = 'dev:guide';
    protected $aliases = ['guide'];
    protected $description = 'Generate automatically docblock for IDE';

    public function handle()
    {
        $guide = match (config('laplus.guide.type')) {
            'docblock' => new DocblockGuide(),
            'mixin'    => new MixinGuide(config('laplus.guide.mixin.path'), config('laplus.guide.mixin.namespace'), config('laplus.guide.mixin.git_ignore')),
            default    => throw new \Exception(sprintf('Guide type [%s] not supported', config('laplus.guide.type'))),
        };

        $authors = [];
        Generate::make()
            ->resolve(Laplus::getResources())
            ->forEachModels(function ($model) use ($guide, &$authors) {
                $authors[] = new ModelAuthor($guide, $model);
            });

        $guide->run($authors);
        $this->components->info("Guide docblock generated successfully!");
    }

}