<?php

namespace Rapid\Laplus\Tests\Resources;

use Rapid\Laplus\LaplusFactory;
use Rapid\Laplus\Resources\FixedResource;
use Rapid\Laplus\Resources\ModularResource;
use Rapid\Laplus\Tests\TestCase;

class ResourceConfigTest extends TestCase
{

    public function test_load_from_config()
    {
        $laplus = new LaplusFactory();

        $laplus->loadConfig([
            'a' => [
                'models' => 'a-models',
                'migrations' => 'a-migrations',
            ],
            'b' => [
                'type' => 'default',
                'models' => 'b-models',
                'migrations' => 'b-migrations',
            ],
            'c' => [
                'type' => FixedResource::class,
                'models' => 'c-models',
                'migrations' => 'c-migrations',
            ],
            'd' => [
                'type' => 'modular',
                'modules' => __DIR__ . '/Modules',
                'models' => 'Models',
                'migrations' => 'Migrations',
            ],
        ]);

        $this->assertSame(FixedResource::class, get_class($laplus->getResource('a')));
        $this->assertSame(FixedResource::class, get_class($laplus->getResource('b')));
        $this->assertSame(FixedResource::class, get_class($laplus->getResource('c')));
        $this->assertSame(ModularResource::class, get_class($laplus->getResource('d')));
        $this->assertSame(null, $laplus->getResource('x'));

        $this->assertSame(['a-models' => 'a-migrations'], $laplus->getResource('a')->resolve());

        $this->assertSame([
            __DIR__ . '/Modules/Bar/Models' => __DIR__ . '/Modules/Bar/Migrations',
            __DIR__ . '/Modules/Foo/Models' => __DIR__ . '/Modules/Foo/Migrations',
        ], $laplus->getResource('d')->resolve());
    }

}