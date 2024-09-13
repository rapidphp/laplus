<?php

namespace Rapid\Laplus\Tests\Resources;

use Rapid\Laplus\Resources\FixedResource;
use Rapid\Laplus\Resources\ModularResource;
use Rapid\Laplus\Tests\TestCase;

class ResourcesTest extends TestCase
{

    public function test_fixed_resource()
    {
        $resource = new FixedResource('Foo', 'Bar');

        $this->assertSame(['Foo' => 'Bar'], $resource->resolve());
    }

    public function test_modular_resource()
    {
        $resource = new ModularResource(__DIR__.'/Modules', 'Models', 'Migrations');

        $this->assertSame([
            __DIR__.'/Modules/Bar/Models' => __DIR__.'/Modules/Bar/Migrations',
            __DIR__.'/Modules/Foo/Models' => __DIR__.'/Modules/Foo/Migrations',
        ], $resource->resolve());
    }

}