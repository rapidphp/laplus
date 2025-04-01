<?php

namespace Rapid\Laplus\Present\Generate;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Fluent;
use Rapid\Laplus\Present\Generate\Structure\DatabaseState;
use Rapid\Laplus\Present\Generate\Structure\TableState;
use RuntimeException;

class SchemaTracker
{

    public DatabaseState $state;

    public array $travels;

    public function reset(?DatabaseState $state = null)
    {
        $this->state = $state ?? new DatabaseState();
        $this->travels = [];
    }

    public function create(string $tableName, Closure $callback)
    {
        $table = new Blueprint($tableName, $callback);

        self::applyBlueprintIntoState($this->state, $table, true);
    }

    public function table(string $tableName, Closure $callback)
    {
        $table = new Blueprint($tableName, $callback);

        self::applyBlueprintIntoState($this->state, $table);
    }

    public function drop(string $tableName)
    {
        unset($this->state->tables[$tableName]);
    }

    public function dispatchTravel(string $relativePath): void
    {
        $this->travels[] = $relativePath;
    }

    public static function applyBlueprintIntoState(DatabaseState $state, Blueprint $table, bool $create = false): void
    {
        $tableName = $table->getTable();

        if ($create) {
            $state->put($tableName, new TableState());
        }

        $renameColumn = [];
        foreach ($table->getCommands() as $command) {
            if ($command instanceof ColumnDefinition) continue;

            switch ($command->name) {
                case 'renameColumn':
                    $column = $state->get($tableName)->columns[$command->from];
                    $state->get($tableName)->columns[$command->to] = $column;
                    $column->name = $command->to;
                    unset($state->get($tableName)->columns[$command->from]);
                    $renameColumn[$command->from] = $command->to;
                    break;

                case 'dropColumn':
                    foreach ($command->columns as $column) {
                        unset($state->get($tableName)->columns[$column]);
                    }
                    break;

                case 'drop':
                    unset($state->tables[$tableName]);
                    break;

                case 'rename':
                    $state->put($command->to, $state->get($tableName));
                    unset($state->tables[$tableName]);
                    $tableName = $command->to;
                    break;

                case 'index':
                case 'fulltext':
                case 'primary':
                case 'foreign':
                case 'unique':
                    $state->get($tableName)->indexes[$command->index] = $command;
                    break;

                case 'dropIndex':
                case 'dropFullText':
                case 'dropPrimary':
                case 'dropForeign':
                    unset($state->get($tableName)->indexes[$command->index]);
                    break;

                case 'renameIndex':
                    $state->get($tableName)->indexes[$command->to] = $state->get($tableName)->indexes[$command->from];
                    $state->get($tableName)->indexes[$command->to]->index = $command->to;
                    unset($state->tables[$tableName]->indexes[$command->from]);
                    break;

                default:
                    throw new RuntimeException("Internal error: unknown command " . print_r($command, true));
            }
        }

        foreach ($table->getColumns() as $column) {
            $state->get($tableName)->columns[$column->name] = $column;

            if ($column->primary) {
                $state->get($tableName)->indexes[$tableName . '_' . $column->name . '_primary'] = new Fluent([
                    "name" => "primary",
                    "columns" => [$column->name],
                    "algorithm" => null,
                ]);
            } elseif ($column->fulltext) {
                $state->get($tableName)->indexes[$tableName . '_' . $column->name . '_fulltext'] = new Fluent([
                    "name" => "fulltext",
                    "columns" => [$column->name],
                    "algorithm" => null,
                ]);
            }
        }
    }

    public static function track(Closure $callback, ?DatabaseState $initialize = null): self
    {
        $defaultSchema = app()->get('db.schema');

        try {
            app()->singleton('db.schema', SchemaTracker::class);

            /** @var SchemaTracker $schema */
            $schema = app('db.schema');
            $schema->reset($initialize);

            $callback();

            return $schema;
        } finally {
            app()->singleton('db.schema', fn() => $defaultSchema);
        }
    }

}