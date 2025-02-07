<?php

namespace Rapid\Laplus\Commands\Make;

use Illuminate\Console\GeneratorCommand;

class LabelTranslatorMakeCommand extends GeneratorCommand
{

    protected $name = 'make:label-translator';
    protected $type = "LabelTranslator";
    protected $description = "Make new label translator class";


    /**
     * Get namespace
     *
     * @param $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\\LabelTranslators';
    }

    /**
     * Get stub
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/label-translator.stub';
    }

}