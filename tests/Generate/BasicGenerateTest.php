<?php

namespace Rapid\Laplus\Tests\Generate;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\Generate\MigrationExporter;
use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\TestCase;
use Rapid\Laplus\Travel\Travel;

class BasicGenerateTest extends TestCase
{

    public function test_basic_generate()
    {
        $generate = new MigrationGenerator();
        $generate->pass([
            _BasicGenerateTestModel::class,
        ]);

        $exporter = new MigrationExporter();
        $files = $exporter->exportMigrationFiles([$generate]);

        $this->assertStringEndsWith('_create_tests_table', array_keys($files->files)[0]);
        $this->assertSame([
            'Schema::create(\'tests\', function (Blueprint $table) {',
            '    $table->bigInteger(\'id\')->autoIncrement()->unsigned();',
            '    $table->text(\'name\');',
            '    $table->integer(\'age\')->unsigned();',
            '    $table->boolean(\'is_male\')->default(false);',
            '    $table->unique([\'name\', \'age\'], \'tests_name_age_unique\');',
            '});',
        ], end($files->files)->up);

        $this->assertSame([
            'Schema::drop(\'tests\');',
        ], end($files->files)->down);
    }

}

class _BasicGenerateTestModel extends Model
{
    use HasPresent;

    protected $table = 'tests';

    protected function present(Present $present)
    {
        $present->id();
        $present->text('name');
        $present->unsignedInteger('age');
        $present->boolean('is_male')->default(false);
        $present->unique(['name', 'age']);
    }
}
