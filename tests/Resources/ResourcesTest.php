<?php

namespace Rapid\Laplus\Tests\Resources;

use Rapid\Laplus\Resources\FixedResource;
use Rapid\Laplus\Resources\ModularResource;
use Rapid\Laplus\Tests\TestCase;

class ResourcesTest extends TestCase
{

    public function test_fixed_resource()
    {
        $resource = new FixedResource('Foo', 'Bar', 'Dev', 'Travels');

        $resolved = $resource->resolve();
        $this->assertCount(1, $resolved);
        $this->assertSame('Foo', $resolved[0]->modelsPath);
        $this->assertSame('Bar', $resolved[0]->migrationsPath);
        $this->assertSame('Dev', $resolved[0]->devPath);
        $this->assertSame('Travels', $resolved[0]->travelsPath);
    }

    public function test_modular_resource()
    {
        $resource = new ModularResource(__DIR__ . '/Modules', 'Models', 'Migrations', 'Dev', 'Travels');

        $resolved = $resource->resolve();
        $this->assertCount(2, $resolved);
        $this->assertSame(__DIR__ . '/Modules/Bar/Models', $resolved[0]->modelsPath);
        $this->assertSame(__DIR__ . '/Modules/Bar/Migrations', $resolved[0]->migrationsPath);
        $this->assertSame(__DIR__ . '/Modules/Bar/Dev', $resolved[0]->devPath);
        $this->assertSame(__DIR__ . '/Modules/Bar/Travels', $resolved[0]->travelsPath);
        $this->assertSame(__DIR__ . '/Modules/Foo/Models', $resolved[1]->modelsPath);
        $this->assertSame(__DIR__ . '/Modules/Foo/Migrations', $resolved[1]->migrationsPath);
        $this->assertSame(__DIR__ . '/Modules/Foo/Dev', $resolved[1]->devPath);
        $this->assertSame(__DIR__ . '/Modules/Foo/Travels', $resolved[1]->travelsPath);
    }

}