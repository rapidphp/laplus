<?php

namespace Rapid\Laplus;

use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;

class LaplusServiceProvider extends ServiceProvider
{

    protected array $commands = [
        Commands\Make\ModelMakeCommand::class,
        Commands\Make\PresentMakeCommand::class,
        Commands\Make\UserPresentMakeCommand::class,
        Commands\Make\LabelTranslatorMakeCommand::class,
        Commands\Dev\DevGuideCommand::class,
        Commands\Dev\DevMigrationCommand::class,
        Commands\Dev\DevMigrateCommand::class,
        Commands\Deploy\DeployMigrationCommand::class,
    ];

    public function register()
    {
        $this->registerConfig();
        $this->registerLang();
        $this->commands($this->commands);
    }

    protected function registerConfig()
    {
        $config = __DIR__ . '/../config/laplus.php';
        $this->publishes([$config => base_path('config/laplus.php')], ['laplus']);
        $this->mergeConfigFrom($config, 'laplus');

        $this->registerResources();
    }

    protected function registerLang()
    {
        $lang = __DIR__ . '/../lang';
        $this->publishes([$lang => $this->app->langPath('vendor/laplus')], ['laplus:lang', 'lang']);
        $this->loadTranslationsFrom($lang, 'laplus');
    }

    protected function registerResources()
    {
        Laplus::loadConfig(config('laplus.resources', []));

        $this->callAfterResolving('migrator', function (Migrator $migrator) {
            foreach (config('laplus.resources', []) as $name => $config) {
                if (@$config['merge_to_config']) {
                    foreach (Laplus::getResource($name)->resolve() as $resource) {
                        $migrator->path($resource->migrationsPath);
                        $migrator->path($resource->devPath);
                    }
                }
            }
        });
    }

}