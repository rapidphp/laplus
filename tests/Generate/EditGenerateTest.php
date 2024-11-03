<?php

namespace Rapid\Laplus\Tests\Generate;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Rapid\Laplus\Present\Generate\MigrationExporter;
use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\TestCase;

class EditGenerateTest extends TestCase
{

    public function test_basic_edit()
    {
        $generate = new MigrationGenerator();
        $generate->resolveTableFromMigration(
            _BasicEditGenerateTestModel::blueprint(...)
        );
        $generate->pass([
            _BasicEditGenerateTestModel::class,
        ]);

        $exporter = new MigrationExporter();
        $files = $exporter->exportMigrationFiles([$generate]);

        $this->assertStringEndsWith('_modify_tests_table', array_keys($files->files)[0]);
        $this->assertSame([
            'Schema::table(\'tests\', function (Blueprint $table) {',
            '    $table->renameColumn(\'aje\', \'age\');',
            '    $table->text(\'name\')->change();',
            '    $table->integer(\'age\')->change();',
            '});',
        ], end($files->files)->up);

        $this->assertSame([
            'Schema::table(\'tests\', function (Blueprint $table) {',
            '    $table->integer(\'age\')->change();',
            '    $table->text(\'name\')->change();',
            '    $table->renameColumn(\'age\', \'aje\');',
            '});',
        ], end($files->files)->down);
    }

}

class _BasicEditGenerateTestModel extends Model
{
    use HasPresent;

    protected $table = 'tests';

    public static function blueprint()
    {
        Schema::create('tests', function (Blueprint $table)
        {
            $table->id();
            $table->string('name');
            $table->integer('aje');
        });
    }

    protected function present(Present $present)
    {
        $present->id();
        $present->text('name');
        $present->integer('age')->old('aje');
    }
}
