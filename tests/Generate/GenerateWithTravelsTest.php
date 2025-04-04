<?php

namespace Rapid\Laplus\Tests\Generate;

use Illuminate\Database\Schema\Blueprint;
use Rapid\Laplus\Present\Generate\MigrationGenerator;
use Rapid\Laplus\Present\Generate\Testing\AnonymousTestingTravel;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\TestCase;
use Rapid\Laplus\Travel\TravelDispatcher;

class GenerateWithTravelsTest extends TestCase
{
    public function test_travel_in_the_beginning()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
            })
            ->newModel('blogs', function (Present $present) {
                $present->string('title');
                $present->unsignedBigInteger('likes');
            })
            ->withTravel('foo_travel', new class extends AnonymousTestingTravel {
                public string|array $on = 'blogs';
                public bool $anywayBefore = true;
            })
            ->export()
            ->assertFileNames(['foo_travel', 'add_likes_to_blogs_table'])
            ->assertModifyTable('blogs')
            ->assertFiles([
                ['up' => [
                    sprintf('\%s::dispatchUp(\'foo_travel\');', TravelDispatcher::class),
                ]],
                ['up.table' => [
                    '$table->bigInteger(\'likes\')->unsigned();',
                ]],
            ]);
    }

    public function test_travel_in_the_finally()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
            })
            ->newModel('blogs', function (Present $present) {
                $present->string('title');
                $present->unsignedBigInteger('likes');
            })
            ->withTravel('foo_travel', new class extends AnonymousTestingTravel {
                public string|array $on = 'blogs';
                public bool $anywayFinally = true;
            })
            ->export()
            ->assertFileNames(['add_likes_to_blogs_table', 'foo_travel'])
            ->assertModifyTable('blogs')
            ->assertFiles([
                ['up.table' => [
                    '$table->bigInteger(\'likes\')->unsigned();',
                ]],
                ['up' => [
                    sprintf('\%s::dispatchUp(\'foo_travel\');', TravelDispatcher::class),
                ]],
            ]);
    }

    public function test_travel_when_added_column()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
            })
            ->newModel('blogs', function (Present $present) {
                $present->string('title');
                $present->unsignedBigInteger('likes');
                $present->text('content');
            })
            ->newModel('users', function (Present $present) {
                $present->string('name');
            })
            ->withTravel('foo_travel', new class extends AnonymousTestingTravel {
                public string|array $on = 'blogs';
                public string|array $whenAdded = 'likes';
            })
            ->export()
            ->assertFileNames(['add_likes_to_blogs_table', 'foo_travel', 'add_content_to_blogs_table', 'create_users_table'])
            ->assertModifyTable('blogs')
            ->assertCreateTable('users')
            ->assertFiles([
                ['up.table' => [
                    '$table->bigInteger(\'likes\')->unsigned();',
                ]],
                ['up' => [
                    sprintf('\%s::dispatchUp(\'foo_travel\');', TravelDispatcher::class),
                ]],
                ['up.table' => [
                    '$table->text(\'content\');',
                ]],
                ['up.table' => [
                    '$table->string(\'name\')->length(255);',
                ]],
            ]);
    }

    public function test_travel_when_removing_column()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
                $table->unsignedBigInteger('likes');
            })
            ->newModel('blogs', function (Present $present) {
                $present->string('title');
            })
            ->newModel('users', function (Present $present) {
                $present->string('name');
            })
            ->withTravel('foo_travel', new class extends AnonymousTestingTravel {
                public string|array $on = 'blogs';
            })
            ->export()
            ->assertFileNames(['foo_travel', 'remove_likes_from_blogs_table', 'create_users_table'])
            ->assertModifyTable('blogs')
            ->assertCreateTable('users')
            ->assertFiles([
                ['up' => [
                    sprintf('\%s::dispatchUp(\'foo_travel\');', TravelDispatcher::class),
                ]],
                ['up.table' => [
                    '$table->dropColumn(\'likes\');',
                ], 'down.table' => [
                    '$table->bigInteger(\'likes\')->unsigned();',
                ]],
                ['up.table' => [
                    '$table->string(\'name\')->length(255);',
                ]],
            ]);
    }

    public function test_travel_when_renamed_column()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
            })
            ->newModel('blogs', function (Present $present) {
                $present->integer('bar')->old('title');
            })
            ->newModel('users', function (Present $present) {
                $present->string('name');
            })
            ->withTravel('foo_travel', new class extends AnonymousTestingTravel {
                public string|array $on = 'blogs';
                public array $whenRenamed = ['title' => 'bar'];
            })
            ->export()
            ->assertFileNames(['rename_title_to_bar_in_blogs_table', 'foo_travel', 'change_bar_type_in_blogs_table', 'create_users_table'])
            ->assertModifyTable('blogs')
            ->assertCreateTable('users')
            ->assertFiles([
                ['up.table' => [
                    '$table->renameColumn(\'title\', \'bar\');',
                ]],
                ['up' => [
                    sprintf('\%s::dispatchUp(\'foo_travel\');', TravelDispatcher::class),
                ]],
                ['up.table' => [
                    '$table->integer(\'bar\')->change();',
                ]],
                ['up.table' => [
                    '$table->string(\'name\')->length(255);',
                ]],
            ]);
    }

    public function test_travel_when_changed_column()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
            })
            ->newModel('blogs', function (Present $present) {
                $present->integer('title');
            })
            ->newModel('users', function (Present $present) {
                $present->string('name');
            })
            ->withTravel('foo_travel', new class extends AnonymousTestingTravel {
                public string|array $on = 'blogs';
                public string|array $whenChanged = 'title';
            })
            ->export()
            ->assertFileNames(['change_title_type_in_blogs_table', 'foo_travel', 'create_users_table'])
            ->assertModifyTable('blogs')
            ->assertCreateTable('users')
            ->assertFiles([
                ['up.table' => [
                    '$table->integer(\'title\')->change();',
                ]],
                ['up' => [
                    sprintf('\%s::dispatchUp(\'foo_travel\');', TravelDispatcher::class),
                ]],
                ['up.table' => [
                    '$table->string(\'name\')->length(255);',
                ]],
            ]);
    }

    public function test_travel_when_renamed_and_changed_column()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
            })
            ->newModel('blogs', function (Present $present) {
                $present->integer('foo')->old('title');
            })
            ->newModel('users', function (Present $present) {
                $present->string('name');
            })
            ->withTravel('foo_travel', new class extends AnonymousTestingTravel {
                public string|array $on = 'blogs';
                public array $whenRenamed = ['title' => 'foo'];
                public string|array $whenChanged = 'foo';
            })
            ->export()
            ->assertFileNames(['rename_title_to_foo_in_blogs_table', 'change_foo_type_in_blogs_table', 'foo_travel', 'create_users_table'])
            ->assertModifyTable('blogs')
            ->assertCreateTable('users')
            ->assertFiles([
                ['up.table' => [
                    '$table->renameColumn(\'title\', \'foo\');',
                ]],
                ['up.table' => [
                    '$table->integer(\'foo\')->change();',
                ]],
                ['up' => [
                    sprintf('\%s::dispatchUp(\'foo_travel\');', TravelDispatcher::class),
                ]],
                ['up.table' => [
                    '$table->string(\'name\')->length(255);',
                ]],
            ]);
    }

    public function test_travel_depended_on_multiple_tables()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->integer('foo');
            })
            ->previousTable('users', function (Blueprint $table) {
                $table->integer('bar');
            })
            ->newModel('blogs', function (Present $present) {
                $present->string('title');
            })
            ->newModel('users', function (Present $present) {
                $present->string('name');
                $present->integer('foo');
                $present->integer('bar');
            })
            ->withTravel('foo_travel', new class extends AnonymousTestingTravel {
                public string|array $on = ['blogs', 'users'];
                public string|array $whenAdded = ['blogs.title', 'users.name'];
            })
            ->export()
            ->assertFileNames(['add_title_to_blogs_table', 'add_name_to_users_table', 'foo_travel', 'remove_foo_from_blogs_table', 'add_foo_to_users_table'])
            ->assertModifyTable('blogs')
            ->assertModifyTable('users')
            ->assertFiles([
                ['up.table' => [
                    '$table->string(\'title\')->length(255);',
                ]],
                ['up.table' => [
                    '$table->string(\'name\')->length(255);',
                ]],
                ['up' => [
                    sprintf('\%s::dispatchUp(\'foo_travel\');', TravelDispatcher::class),
                ]],
                ['up.table' => [
                    '$table->dropColumn(\'foo\');',
                ]],
                ['up.table' => [
                    '$table->integer(\'foo\');',
                ]],
            ]);
    }

    public function test_travel_when_added_with_prepare_nullable_column()
    {
        MigrationGenerator::test()
            ->previousTable('blogs', function (Blueprint $table) {
                $table->string('title');
            })
            ->newModel('blogs', function (Present $present) {
                $present->string('title');
                $present->unsignedBigInteger('likes');
            })
            ->withTravel('foo_travel', new class extends AnonymousTestingTravel {
                public string|array $on = 'blogs';
                public string|array $whenAdded = 'likes';
                public string|array $prepareNullable = 'likes';
            })
            ->export()
            ->assertFileNames(['add_likes_to_blogs_table', 'foo_travel', 'change_likes_nullable_in_blogs_table'])
            ->assertModifyTable('blogs')
            ->assertFiles([
                ['up.table' => [
                    '$table->bigInteger(\'likes\')->unsigned()->nullable();',
                ]],
                ['up' => [
                    sprintf('\%s::dispatchUp(\'foo_travel\');', TravelDispatcher::class),
                ]],
                ['up.table' => [
                    '$table->bigInteger(\'likes\')->unsigned()->change();',
                ]],
            ]);
    }

}