<?php

namespace Rapid\Laplus\Commands;

use Illuminate\Console\GeneratorCommand;

class LaplusPresentMakeCommand extends GeneratorCommand
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