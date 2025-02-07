<?php

namespace Rapid\Laplus\Commands\Make;

use Illuminate\Console\GeneratorCommand;

class PresentMakeCommand extends GeneratorCommand
{

    protected $name = 'make:present';
    protected $type = "Present";
    protected $description = "Make new present class";


    /**
     * Get namespace
     *
     * @param $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\Presents';
    }

    /**
     * Get stub
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/present.stub';
    }

}