<?php

namespace Rapid\Laplus\Tests;

use Illuminate\Database\Schema\Blueprint;

class TestTemp extends TestCase
{

    public function test_jkasd()
    {
        $table = new Blueprint('test');
        $table->unique(['a', 'b']);
        $table->primary(['a', 'b']);
        $table->spatialIndex(['a', 'b'])->language('php');

        dd($table);
    }

}