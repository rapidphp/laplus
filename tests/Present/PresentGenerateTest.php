<?php

namespace Rapid\Laplus\Tests\Present;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Rapid\Laplus\Present\Generate\MigrationExporter;
use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\TestCase;

class PresentGenerateTest extends TestCase
{

    public static $__present;
    public static $__table;

    public function generate(
        Closure $schema,
        Closure $present,
        string  $table = 'tests',
    )
    {
        $generator = new MigrationGenerator();

        $generator->resolveTableFromMigration(function () use ($schema, $table) {
            Schema::create($table, $schema);
        });

        static::$__present = $present;
        static::$__table = $table;

        $generator->pass([
            new class extends Model {
                use HasPresent;

                protected function present(Present $present)
                {
                    (PresentGenerateTest::$__present)($present);
                }

                public function getTable()
                {
                    return PresentGenerateTest::$__table;
                }
            },
        ]);

        $exporter = new MigrationExporter();

        return $exporter->exportMigrationStubs(
            $exporter->exportMigrationFiles([$generator]),
        );
    }

    public function test_no_change()
    {
        $this->assertEmpty(
            $this->generate(
                function (Blueprint $table) {
                    $table->id();
                    $table->text('test');
                },
                function (Present $table) {
                    $table->id();
                    $table->text('test');
                },
            ),
        );
    }

    public function test_rename()
    {
        $stubs = $this->generate(
            function (Blueprint $table) {
                $table->text('foo');
            },
            function (Present $table) {
                $table->text('bar')->old('foo');
            },
        );

        $this->assertCount(1, $stubs);
        $this->assertStringContainsString('rename_foo_to_bar', array_keys($stubs)[0]);
        $this->assertStringContainsString('$table->renameColumn(\'foo\', \'bar\');', head($stubs));
    }

    public function test_change_type()
    {
        $stubs = $this->generate(
            function (Blueprint $table) {
                $table->integer('foo');
            },
            function (Present $table) {
                $table->text('foo');
            },
        );

        $this->assertCount(1, $stubs);
        $this->assertStringContainsString('change_foo_type', array_keys($stubs)[0]);
        $this->assertStringContainsString('$table->text(\'foo\')->change();', head($stubs));
    }

    public function test_add_column()
    {
        $stubs = $this->generate(
            function (Blueprint $table) {
            },
            function (Present $table) {
                $table->text('foo');
            },
        );

        $this->assertCount(1, $stubs);
        $this->assertStringContainsString('add_foo', array_keys($stubs)[0]);
        $this->assertStringContainsString('$table->text(\'foo\');', head($stubs));
    }

    public function test_remove_column()
    {
        $stubs = $this->generate(
            function (Blueprint $table) {
                $table->id();
                $table->text('foo');
            },
            function (Present $table) {
                $table->id();
            },
        );

        $this->assertCount(1, $stubs);
        $this->assertStringContainsString('remove_foo', array_keys($stubs)[0]);
        $this->assertStringContainsString('$table->dropColumn(\'foo\');', head($stubs));
    }

}