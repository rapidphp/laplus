<?php

namespace Rapid\Laplus\Tests;

use Rapid\Laplus\LaplusServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            ...parent::getPackageProviders($app),
            LaplusServiceProvider::class,
        ];
    }

}