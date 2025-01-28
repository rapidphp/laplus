<?php

namespace Rapid\Laplus\Present\Generate\Concerns;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Rapid\Laplus\Present\Generate\SchemaCollectingData;

trait MigrationResolves
{

    /**
     * Resolve current table status from migrations
     *
     * @param Closure $callback
     * @return void
     */
    public function resolveTableFromMigration(Closure $callback)
    {
        $laravelSchema = app()->get('db.schema');
        app()->singleton('db.schema', SchemaCollectingData::class);

        /** @var SchemaCollectingData $schema */
        $schema = app('db.schema');
        $schema->reset();

        $callback();

        app()->singleton('db.schema', get_class($laravelSchema));

        $this->definedMigrationState = $schema->state;
    }

    /**
     * Resolve current table status from database
     *
     * @return void
     */
    public function resolveTableFromDatabase()
    {
        // TODO : This function is soon feature
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->text('name');
        });
        $this->definedMigrationState = [];
        foreach (Schema::getTables() as $table) {
            $name = $table['name'];

            foreach (Schema::getColumns($name) as $column) {
                @$this->definedMigrationState[$name]['columns'][$column['name']] =
                    new ColumnDefinition(Arr::mapWithKeys($column, fn($value, $key) => [Str::camel($key) => $value]));
            }

            foreach (Schema::getIndexes($name) as $index) {
                @$this->definedMigrationState[$name]['indexes'][$index['name']] =
                    new ColumnDefinition(Arr::mapWithKeys($index, fn($value, $key) => [Str::camel($key) => $value]));
            }
        }
    }

}