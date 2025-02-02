<?php

namespace Rapid\Laplus\Present\Generate;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Fluent;
use Rapid\Laplus\Present\Generate\Structure\DefinedMigrationState;
use Rapid\Laplus\Present\Generate\Structure\DefinedTableState;

class SchemaCollectingData
{

    public DefinedMigrationState $state;

    public function reset()
    {
        $this->state = new DefinedMigrationState();
    }

    public function create(string $tableName, Closure $callback)
    {
        $this->state->put($tableName, new DefinedTableState());

        $this->table($tableName, $callback);
    }

    public function table(string $tableName, Closure $callback)
    {
        $table = new Blueprint($tableName, $callback);

        $renameColumn = [];
        foreach ($table->getCommands() as $command) {
            if ($command instanceof ColumnDefinition) continue;

            switch ($command->name) {
                case 'renameColumn':
                    $column = $this->state->get($tableName)->columns[$command->from];
                    $this->state->get($tableName)->columns[$command->to] = $column;
                    $column->name = $command->to;
                    unset($this->state->get($tableName)->columns[$command->from]);
                    $renameColumn[$command->from] = $command->to;
                    break;

                case 'dropColumn':
                    foreach ($command->columns as $column) {
                        unset($this->state->get($tableName)->columns[$column]);
                    }
                    break;

                case 'drop':
                    unset($this->state->tables[$tableName]);
                    break;

                case 'rename':
                    $this->state->put($command->to, $this->state->get($tableName));
                    unset($this->state->tables[$tableName]);
                    $tableName = $command->to;
                    break;

                case 'index':
                case 'fulltext':
                case 'primary':
                case 'foreign':
                case 'unique':
                    $this->state->get($tableName)->indexes[$command->index] = $command;
                    break;

                case 'dropIndex':
                case 'dropFullText':
                case 'dropPrimary':
                case 'dropForeign':
                    unset($this->state->get($tableName)->indexes[$command->index]);
                    break;

                case 'renameIndex':
                    $this->state->get($tableName)->indexes[$command->to] = $this->state->get($tableName)->indexes[$command->from];
                    $this->state->get($tableName)->indexes[$command->to]->index = $command->to;
                    unset($this->state->tables[$tableName]->indexes[$command->from]);
                    break;

                default:
                    dd("Command", $command); // TODO : Unknown command
            }
        }

        foreach ($table->getColumns() as $column) {
            $this->state->get($tableName)->columns[$column->name] = $column;

            if ($column->primary) {
                $this->state->get($tableName)->indexes[$tableName . '_' . $column->name . '_primary'] = new Fluent([
                    "name" => "primary",
                    "columns" => [$column->name],
                    "algorithm" => null,
                ]);
            } elseif ($column->fulltext) {
                $this->state->get($tableName)->indexes[$tableName . '_' . $column->name . '_fulltext'] = new Fluent([
                    "name" => "fulltext",
                    "columns" => [$column->name],
                    "algorithm" => null,
                ]);
            }
        }
    }

    public function drop(string $tableName)
    {
        unset($this->state->tables[$tableName]);
    }

}