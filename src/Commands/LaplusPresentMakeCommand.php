<?php

namespace Rapid\Laplus\Commands;

use Illuminate\Console\GeneratorCommand;

class LaplusPresentMakeCommand extends GeneratorCommand
{

    protected $name = 'make:present';

    protected $description = "Make new present class";


    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\\Presents';
    }

    protected function getStub()
    {
        return __DIR__ . '/stubs/present.stub';
    }

}