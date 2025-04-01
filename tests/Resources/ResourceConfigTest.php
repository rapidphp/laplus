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
                'dev_migrations' => 'a-dev-migrations',
                'travels' => 'a-travels',
            ],
            'b' => [
                'type' => 'default',
                'models' => 'b-models',
                'migrations' => 'b-migrations',
                'dev_migrations' => 'b-dev-migrations',
                'travels' => 'b-travels',
            ],
            'c' => [
                'type' => FixedResource::class,
                'models' => 'c-models',
                'migrations' => 'c-migrations',
                'dev_migrations' => 'c-dev-migrations',
                'travels' => 'c-travels',
            ],
            'd' => [
                'type' => 'modular',
                'modules' => __DIR__ . '/Modules',
                'models' => 'Models',
                'migrations' => 'Migrations',
                'dev_migrations' => 'DevMigrations',
                'travels' => 'Travels',
            ],
        ]);

        $this->assertSame(FixedResource::class, get_class($laplus->getResource('a')));
        $this->assertSame(FixedResource::class, get_class($laplus->getResource('b')));
        $this->assertSame(FixedResource::class, get_class($laplus->getResource('c')));
        $this->assertSame(ModularResource::class, get_class($laplus->getResource('d')));
        $this->assertSame(null, $laplus->getResource('x'));

        $resolved = $laplus->getResource('a')->resolve();
        $this->assertCount(1, $resolved);
        $this->assertSame('a-models', $resolved[0]->modelsPath);
        $this->assertSame('a-migrations', $resolved[0]->migrationsPath);
        $this->assertSame('a-dev-migrations', $resolved[0]->devMigrationsPath);
        $this->assertSame('a-travels', $resolved[0]->travelsPath);

        $resolved = $laplus->getResource('d')->resolve();
        $this->assertCount(2, $resolved);
        $this->assertSame(__DIR__ . '/Modules/Bar/Models', $resolved[0]->modelsPath);
        $this->assertSame(__DIR__ . '/Modules/Bar/Migrations', $resolved[0]->migrationsPath);
        $this->assertSame(__DIR__ . '/Modules/Bar/DevMigrations', $resolved[0]->devMigrationsPath);
        $this->assertSame(__DIR__ . '/Modules/Bar/Travels', $resolved[0]->travelsPath);
        $this->assertSame(__DIR__ . '/Modules/Foo/Models', $resolved[1]->modelsPath);
        $this->assertSame(__DIR__ . '/Modules/Foo/Migrations', $resolved[1]->migrationsPath);
        $this->assertSame(__DIR__ . '/Modules/Foo/DevMigrations', $resolved[1]->devMigrationsPath);
        $this->assertSame(__DIR__ . '/Modules/Foo/Travels', $resolved[1]->travelsPath);
    }

}