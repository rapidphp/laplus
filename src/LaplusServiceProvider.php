<?php

namespace Rapid\Laplus;

use Illuminate\Support\ServiceProvider;
use Rapid\Laplus\Laplus;

class LaplusServiceProvider extends ServiceProvider
{

    protected array $commands = [
        Commands\LaplusGenerateCommand::class,
        Commands\LaplusRegenerateCommand::class,
        Commands\LaplusMigrateCommand::class,
        Commands\LaplusModelMakeCommand::class,
        Commands\LaplusPresentMakeCommand::class,
    ];

    public function register()
    {
        $this->registerConfig();
        $this->commands($this->commands);
    }

    public function registerConfig()
    {
        $config = __DIR__ . '/../config/laplus.php';

        $this->publishes([$config => base_path('config/laplus.php')], ['laplus']);

        $this->mergeConfigFrom($config, 'laplus');

        Laplus::mergeResources(config()->get('laplus.resources', []));
    }

}