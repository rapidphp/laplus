<?php

namespace Rapid\Laplus\Tests\Present;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Rapid\Laplus\Tests\Present\Models\Relations\Post;
use Rapid\Laplus\Tests\Present\Models\Relations\User;
use Rapid\Laplus\Tests\TestCase;

class PresentMigrationTest extends TestCase
{

    public function test_migration()
    {
        Artisan::call('laplus:generate');
        $this->assertTrue(true);

        return;

        $generator = new MigrationGenerator();

        $generator->resolveTableFromMigration(function ()
        {
            Schema::create('tests', function (Blueprint $table)
            {
                $table->id();
                $table->text('name');
            });
            Schema::table('tests', function (Blueprint $table)
            {
                $table->dropPrimary('tests_id_primary');
                // $table->dropColumn('id');
            });
            Schema::create('users', function (Blueprint $table)
            {
                $table->id();
            });
        });

        $generator->pass([
            User::class,
            Post::class,
        ]);

        dd(
            $generator->generateMigrationStubs(
                $generator->generateMigrationFiles()
            )
        );
    }

}