<?php

namespace Rapid\Laplus\Tests\Generate;

use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\TestCase;

class FlowGenerateTest extends TestCase
{
    public function test_create_new_tables()
    {
        MigrationGenerator::test()
            ->newModel('blogs', function (Present $present) {
                $present->string('title');
                $present->unsignedBigInteger('likes');
            })
            ->export()
            ->assertCreateTable('blogs');
    }
}