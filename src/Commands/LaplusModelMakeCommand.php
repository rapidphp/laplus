<?php

namespace Rapid\Laplus\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class LaplusModelMakeCommand extends ModelMakeCommand
{

    protected $name = 'make:model-laplus';
    protected $aliases = ['make:model+'];

    public function handle()
    {
        if (parent::handle() === false && !$this->option('force')) {
            return false;
        }

        $this->createPresent();
        $this->createLabelTranslator();
    }

    /**
     * Create a controller for the model.
     *
     * @return void
     */
    protected function createPresent()
    {
        if (!$this->option('inline')) {
            $modelClass = Str::studly(class_basename($this->argument('name')));

            $modelName = $this->qualifyClass($this->getNameInput());

            $this->call('make:present', array_filter([
                'name' => "{$modelClass}Present",
                // '--model' => $this->option('resource') || $this->option('api') ? $modelName : null,
                // '--api' => $this->option('api'),
                // '--requests' => $this->option('requests') || $this->option('all'),
                // '--test' => $this->option('test'),
                // '--pest' => $this->option('pest'),
            ]));
        }
    }

    /**
     * Create a label translator for the model.
     *
     * @return void
     */
    protected function createLabelTranslator()
    {
        if ($this->option('label')) {
            $modelClass = Str::studly(class_basename($this->argument('name')));

            $modelName = $this->qualifyClass($this->getNameInput());

            $this->call('make:label-translator', array_filter([
                'name' => "{$modelClass}LabelTranslator",
                // '--model' => $this->option('resource') || $this->option('api') ? $modelName : null,
                // '--api' => $this->option('api'),
                // '--requests' => $this->option('requests') || $this->option('all'),
                // '--test' => $this->option('test'),
                // '--pest' => $this->option('pest'),
            ]));
        }
    }

    protected function getStub()
    {
        if ($this->option('inline') && $this->option('label')) {
            return __DIR__ . '/stubs/model-inline-with-label.stub';
        } elseif ($this->option('inline')) {
            return __DIR__ . '/stubs/model-inline.stub';
        } elseif ($this->option('label')) {
            return __DIR__ . '/stubs/model-with-label.stub';
        } else {
            return __DIR__ . '/stubs/model.stub';
        }
    }

    protected function getOptions()
    {
        return [
            ...parent::getOptions(),
            ['inline', 'i', InputOption::VALUE_NONE, 'Make present inline'],
            ['label', 'l', InputOption::VALUE_NONE, 'Include making label translator'],
        ];
    }

}