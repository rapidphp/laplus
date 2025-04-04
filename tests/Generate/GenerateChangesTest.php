<?php

namespace Rapid\Laplus\Tests\Generate;

use Illuminate\Database\Schema\Blueprint;
use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\TestCase;

class GenerateChangesTest extends TestCase
{
    public function test_create_new_tables()
    {
        MigrationGenerator::test()
            ->newModel('blogs', function (Present $present) {
                $present->string('title');
                $present->unsignedBigInteger('likes');
            })
            ->export()
            ->assertFileNames(['create_blogs_table'])
            ->assertCreateTable('blogs')
            ->assertFiles([
                ['up.table' => [
                    '$table->string(\'title\')->length(255);',
                    '$table->bigInteger(\'likes\')->unsigned();',
                ]],
            ]);
    }

    public function test_drop_tables()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
            })
            ->export()
            ->assertFileNames(['drop_blogs_table'])
            ->assertDropTable('blogs');
    }

    public function test_no_changes_in_tables()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
            })
            ->newModel('blogs', function (Present $present) {
                $present->string('title');
            })
            ->export()
            ->assertFileCount(0);
    }

    public function test_add_column_in_tables()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
            })
            ->newModel('blogs', function (Present $present) {
                $present->string('title');
                $present->unsignedBigInteger('likes');
            })
            ->export()
            ->assertFileNames(['add_likes_to_blogs_table'])
            ->assertModifyTable('blogs')
            ->assertFiles([
                ['up.table' => [
                    '$table->bigInteger(\'likes\')->unsigned();',
                ]],
            ]);
    }

    public function test_drop_column_in_tables()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
                $table->bigInteger('likes')->unsigned();
            })
            ->newModel('blogs', function (Present $present) {
                $present->string('title');
            })
            ->export()
            ->assertFileNames(['remove_likes_from_blogs_table'])
            ->assertModifyTable('blogs')
            ->assertFiles([
                ['up.table' => [
                    '$table->dropColumn(\'likes\');',
                ]],
            ]);
    }

    public function test_change_column_in_tables()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->bigInteger('likes');
            })
            ->newModel('blogs', function (Present $present) {
                $present->unsignedBigInteger('likes');
            })
            ->export()
            ->assertFileNames(['change_likes_unsigned_in_blogs_table'])
            ->assertModifyTable('blogs')
            ->assertFiles([
                ['up.table' => [
                    '$table->bigInteger(\'likes\')->unsigned()->change();',
                ]],
            ]);
    }

    public function test_rename_column_in_tables()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('name');
            })
            ->newModel('blogs', function (Present $present) {
                $present->string('title')->old('name');
            })
            ->export()
            ->assertFileNames(['rename_name_to_title_in_blogs_table'])
            ->assertModifyTable('blogs')
            ->assertFiles([
                ['up.table' => [
                    '$table->renameColumn(\'name\', \'title\');',
                ]],
            ]);
    }

    public function test_change_and_rename_column_in_tables()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('dollar');
            })
            ->newModel('blogs', function (Present $present) {
                $present->integer('balance')->old('dollar');
            })
            ->export()
            ->assertFileNames(['rename_dollar_to_balance_in_blogs_table', 'change_balance_type_in_blogs_table'])
            ->assertModifyTable('blogs')
            ->assertFiles([
                ['up.table' => [
                    '$table->renameColumn(\'dollar\', \'balance\');',
                ]],
                ['up.table' => [
                    '$table->integer(\'balance\')->change();',
                ]],
            ]);
    }

    public function test_multiple_changes_in_tables()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
                $table->string('dollar');
                $table->string('rial');
            })
            ->newModel('blogs', function (Present $present) {
                $present->integer('balance')->old('dollar');
                $present->integer('balance2')->old('rial');
                $present->string('content');
            })
            ->export()
            ->assertFileNames(['rename_columns_in_blogs_table', 'modify_blogs_table'])
            ->assertModifyTable('blogs')
            ->assertFiles([
                ['up.table' => [
                    '$table->renameColumn(\'dollar\', \'balance\');',
                    '$table->renameColumn(\'rial\', \'balance2\');',
                ]],
                ['up.table' => [
                    '$table->dropColumn(\'title\');',
                    '$table->string(\'content\')->length(255);',
                    '$table->integer(\'balance\')->change();',
                    '$table->integer(\'balance2\')->change();',
                ]],
            ]);
    }
}