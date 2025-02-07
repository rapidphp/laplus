<?php

namespace Rapid\Laplus\Commands\Dev;

use Illuminate\Console\Command;
use Rapid\Laplus\Editors\GitIgnoreEditor;
use Rapid\Laplus\Laplus;
use Rapid\Laplus\Present\Generate\Generate;

class DevMigrationCommand extends Command
{

    protected $signature = 'dev:migration';

    protected $description = 'Generate migration file';

    public function handle()
    {
        if (config('laplus.dev.git_ignore')) {
            GitIgnoreEditor::make(config('laplus.dev.migrations') . '/,gitignore')
                ->add('*')
                ->save();
        }

        Generate::make()
            ->resolve(Laplus::getResources())
            ->export(config('laplus.dev.migrations'));

        $this->components->info('Migration created successfully!');
    }

}