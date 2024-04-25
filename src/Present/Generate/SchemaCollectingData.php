<?php

namespace Rapid\Laplus\Present\Generate;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;

class SchemaCollectingData
{

    public array $tables;

    public function reset()
    {
        $this->tables = [];
    }

    public function create(string $tableName, Closure $callback)
    {
        $this->tables[$tableName] = [
            'columns' => [],
            'indexes' => [],
        ];

        $this->table($tableName, $callback);
    }

    public function table(string $tableName, Closure $callback)
    {
        $table = new Blueprint($tableName, $callback);

        $renameColumn = [];
        foreach ($table->getCommands() as $command)
        {
            switch ($command->name)
            {
                case 'renameColumn':
                    $this->tables[$tableName]['columns'][$command->to] = $this->tables[$tableName]['columns'][$command->from];
                    $this->tables[$tableName]['columns'][$command->to]->name = $command->to;
                    unset($this->tables[$tableName]['columns'][$command->from]);
                    $renameColumn[$command->from] = $command->to;
                    break;

                case 'dropColumn':
                    foreach ($command->columns as $column)
                    {
                        unset($this->tables[$tableName]['columns'][$column]);
                    }
                    break;

                case 'drop':
                    unset($this->tables[$tableName]);
                    break;

                case 'rename':
                    $this->tables[$command->to] = $this->tables[$tableName];
                    unset($this->tables[$tableName]);
                    $tableName = $command->to;
                    break;

                case 'index':
                case 'fulltext':
                case 'primary':
                case 'foreign':
                    $this->tables[$tableName]['indexes'][$command->index] = $command;
                    break;

                case 'dropIndex':
                case 'dropFullText':
                case 'dropPrimary':
                case 'dropForeign':
                    unset($this->tables[$tableName]['indexes'][$command->index]);
                    break;

                case 'renameIndex':
                    $this->tables[$tableName]['indexes'][$command->to] = $this->tables[$tableName]['indexes'][$command->from];
                    $this->tables[$tableName]['indexes'][$command->to]->index = $command->to;
                    unset($this->tables[$tableName]['indexes'][$command->from]);
                    break;

                default:
                    dd("Command", $command);
            }
        }

        foreach ($table->getColumns() as $column)
        {
            $this->tables[$tableName]['columns'][$column->name] = $column;

            if ($column->primary)
            {
                $this->tables[$tableName]['indexes'][$tableName . '_' . $column->name . '_primary'] = new Fluent([
                    "name" => "primary",
                    "columns" => [$column->name],
                    "algorithm" => null
                ]);
            }
            elseif ($column->fulltext)
            {
                $this->tables[$tableName]['indexes'][$tableName . '_' . $column->name . '_fulltext'] = new Fluent([
                    "name" => "fulltext",
                    "columns" => [$column->name],
                    "algorithm" => null
                ]);
            }
        }
    }

    public function drop(string $tableName)
    {
        unset($this->tables[$tableName]);
    }

}