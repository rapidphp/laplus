<?php

namespace Rapid\Laplus\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Laplus;

abstract class LaplusBaseResourceCommand extends Command
{

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
            $config = Laplus::getResource($name);

            if (!$config)
            {
                $this->error("Laplus resource [$name] not found");
                return 1;
            }

            return $this->generateUsing($config);
        }
        // Entered migrations & models options -> Using input
        elseif ($this->option('migrations') || $this->option('models'))
        {
            return $this->generate(
                $this->option('models'),
                $this->option('migrations'),
            );
        }
        // Using all resources
        else
        {
            $resource = Laplus::getResources();
            if (!$resource)
            {
                $this->error("Missing resource configuration. fill 'laplus.resources' config");
                return 1;
            }

            foreach ($resource as $config)
            {
                $this->generateUsing($config);
            }
            return 0;
        }
    }

    /**
     * Generate using resource value
     *
     * @param $config
     * @return int
     */
    public function generateUsing($config)
    {
        if (!is_array($config) || !is_string(@$config['models']) || !is_string(@$config['migrations']))
        {
            $this->error("Invalid configuration");
            return 1;
        }

        return $this->generate($config['models'], $config['migrations']);
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
                yield from $this->discoverModels($subPath);
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
                yield from $this->discoverMigrations($subPath);
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