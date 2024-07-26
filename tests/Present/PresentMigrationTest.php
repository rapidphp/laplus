<?php

namespace Rapid\Laplus\Tests\Present;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Rapid\Laplus\Tests\Present\Model\ModelLaplusFirst;
use Rapid\Laplus\Tests\Present\Model\ModelLaplusPresentable;
use Rapid\Laplus\Tests\Present\Model\ModelLaplusSecond;
use Rapid\Laplus\Tests\Present\Models\Relations\Post;
use Rapid\Laplus\Tests\Present\Models\Relations\User;
use Rapid\Laplus\Tests\TestCase;

class PresentMigrationTest extends TestCase
{

    public function test_migration()
    {
        $generator = new MigrationGenerator();

        $generator->resolveTableFromMigration(function ()
        {
            Schema::create('tests', function (Blueprint $table)
            {
                $table->id();
                $table->text('wants_to_rename');
                $table->text('wants_to_change_type');
                $table->text('wants_to_remove');
            });
        });

        $generator->pass([
            // ModelLaplusFirst::class,
            // ModelLaplusSecond::class,
            ModelLaplusPresentable::class,
        ]);

        dd(
            $generator->generateMigrationStubs(
                $generator->generateMigrationFiles()
            )
        );
    }

}