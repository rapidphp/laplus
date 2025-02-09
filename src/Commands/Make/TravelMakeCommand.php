<?php

namespace Rapid\Laplus\Commands\Make;

use Illuminate\Console\GeneratorCommand;

class TravelMakeCommand extends GeneratorCommand
{

    protected $name = 'make:travel';
    protected $type = "Travel";
    protected $description = "Make a new travel";

    protected function getPath($name)
    {
        $time = date('Y_m_d_His');

        return database_path("travels/{$time}_{$name}.php");
    }

    /**
     * Get stub
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/travel.stub';
    }

}