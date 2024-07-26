<?php

namespace Rapid\Laplus\Commands;

use Symfony\Component\Console\Input\InputArgument;

class LaplusUserPresentMakeCommand extends LaplusPresentMakeCommand
{

    protected $name = 'make:user-present';

    protected $description = "Make new user-present class";

    public function handle()
    {
        if (!$this->argument('name'))
        {
            $this->input->setArgument('name', 'UserPresent');
        }

        return parent::handle();
    }

    /**
     * Get arguments
     *
     * @return array[]
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, 'The name of the '.strtolower($this->type)],
        ];
    }

    /**
     * Get stub
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/user-present.stub';
    }

}