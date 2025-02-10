<?php

namespace Rapid\Laplus\Present\Generate\Concerns;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Rapid\Laplus\Present\Generate\SchemaTracker;
use Rapid\Laplus\Present\Generate\Structure\DatabaseState;

trait MigrationResolves
{

    /**
     * The resolved database state
     *
     * @var DatabaseState
     */
    public DatabaseState $resolvedState;

    /**
     * The resolved travel names
     *
     * @var string[]
     */
    public array $resolvedTravels;

    /**
     * Resolve current table status from migrations
     *
     * @param Closure $callback
     * @return void
     */
    public function resolveTableFromMigration(Closure $callback): void
    {
        $this->resolvedState ??= new DatabaseState();

        $schema = SchemaTracker::track($callback, $this->resolvedState);

        $this->resolvedTravels = $schema->travels;
    }

    /**
     * Resolve current table status from database
     *
     * @return void
     */
    public function resolveTableFromDatabase(): void
    {
        // TODO : This function is soon feature
//        Schema::create('users', function (Blueprint $table) {
//            $table->id();
//            $table->text('name');
//        });
//        $this->resolvedState = [];
//        foreach (Schema::getTables() as $table) {
//            $name = $table['name'];
//
//            foreach (Schema::getColumns($name) as $column) {
//                @$this->resolvedState[$name]['columns'][$column['name']] =
//                    new ColumnDefinition(Arr::mapWithKeys($column, fn($value, $key) => [Str::camel($key) => $value]));
//            }
//
//            foreach (Schema::getIndexes($name) as $index) {
//                @$this->resolvedState[$name]['indexes'][$index['name']] =
//                    new ColumnDefinition(Arr::mapWithKeys($index, fn($value, $key) => [Str::camel($key) => $value]));
//            }
//        }
    }

}