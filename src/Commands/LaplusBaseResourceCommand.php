<?php

namespace Rapid\Laplus\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Laplus;
use Rapid\Laplus\Resources\Resource;

abstract class LaplusBaseResourceCommand extends Command
{

    /**
     * @var Resource[]
     */
    public array $resources;

    /**
     * Handle resource command
     *
     * @return int
     */
    public function handle()
    {
        // Entered name option -> Using special resource
        if ($name = $this->option('name'))
        {
            $resource = Laplus::getResource($name);

            if (!$resource)
            {
                $this->error("Laplus resource [$name] not found");
                return 1;
            }

            $this->runGenerate([$resource]);
            return 0;
        }
        // Entered migrations & models options -> Using input
        elseif ($this->option('migrations') || $this->option('models'))
        {
            $this->generateAll([
                $this->option('models') => $this->option('migrations')
            ]);
            return 0;
        }
        // Using all resources
        else
        {
            $resources = Laplus::getResources();
            if (!$resources)
            {
                $this->error("Missing resource configuration. fill the [laplus.resources] config");
                return 1;
            }

            $this->runGenerate($resources);
            return 0;
        }
    }

    public function runGenerate(array $resources)
    {
        $this->resources = $resources;

        $map = collect($this->resources)
            ->mapWithKeys(fn (Resource $resource) => $resource->resolve())
            ->toArray();

        $this->generateAll($map);
    }

    public function generateAll(array $map)
    {
        foreach ($map as $modelsPath => $migrationsPath)
        {
            $this->generate($modelsPath, $migrationsPath);
        }
    }

    /**
     * Generate using two paths
     *
     * @param string $modelPath
     * @param string $migrationPath
     * @return int
     */
    public abstract function generate(string $modelPath, string $migrationPath);

    protected function discoverModels(string $path)
    {
        foreach (scandir($path) as $sub)
        {
            if ($sub == '.' || $sub == '..')
                continue;

            $subPath = $path . '/' . $sub;

            if (is_dir($subPath))
            {
                foreach ($this->discoverModels($subPath) as $model)
                {
                    yield $model;
                }
            }
            else
            {
                if (str_ends_with($sub, '.php'))
                {
                    $contents = @file_get_contents($subPath);
                    if (
                        preg_match('/namespace\s+(.*?)\s*;/', $contents, $namespaceMatch) &&
                        preg_match('/class\s+(.*?)[\s\n\r{]/', $contents, $classMatch)
                    )
                    {
                        $class = $namespaceMatch[1] . "\\" . $classMatch[1];
                        if (
                            class_exists($class) &&
                            is_a($class, Model::class, true) &&
                            in_array(HasPresent::class, class_uses_recursive($class))
                        )
                        {
                            yield $class;
                        }
                    }
                }
            }
        }
    }

    protected function discoverMigrations(string $path)
    {
        foreach (scandir($path) as $sub)
        {
            if ($sub == '.' || $sub == '..')
                continue;

            $subPath = $path . '/' . $sub;

            if (is_dir($subPath))
            {
                foreach ($this->discoverMigrations($subPath) as $migration)
                {
                    yield $migration;
                }
            }
            else
            {
                if (str_ends_with($sub, '.php'))
                {
                    $value = include $subPath;
                    if ($value instanceof Migration)
                    {
                        yield $value;
                    }
                }
            }
        }
    }

    protected function makeReadyToWrite(string $path)
    {
        if (file_exists($path)) return;

        $path = str_replace('\\', '/', $path);
        if (substr_count($path, '/') > 2)
        {
            $this->makeReadyToWrite(Str::beforeLast($path, '/'));
        }

        @mkdir($path);
    }

}