<?php

namespace Rapid\Laplus\Present\Generate;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Rapid\Laplus\Present\Present;

class MigrationGenerator
{

    /**
     * @var bool
     */
    public bool $includeDropTables = true;

    /**
     * @var Blueprint[]
     */
    public array $tables = [];

    public function getTable(string $name)
    {
        return $this->tables[$name] ??= new Blueprint($name);
    }


    public function pass(array $models)
    {
        foreach ($models as $model)
        {
            /** @var Present $present */
            $present = $model::getPresentInstance()->getPresentObject();

            $present->passGenerator($this);
        }
    }


    protected array $migrationTables;

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

        $schema = app('db.schema');
        $schema->reset();

        $callback();

        app()->singleton('db.schema', get_class($laravelSchema));

        $this->migrationTables = $schema->tables;
    }

    /**
     * Resolve current table status from database
     *
     * @return void
     */
    public function resolveTableFromDatabase()
    {
        Schema::create('users', function (Blueprint $table)
        {
            $table->id();
            $table->text('name');
        });
        $this->migrationTables = [];
        foreach (Schema::getTables() as $table)
        {
            $name = $table['name'];

            foreach (Schema::getColumns($name) as $column)
            {
                @$this->migrationTables[$name]['columns'][$column['name']] =
                    new ColumnDefinition(Arr::mapWithKeys($column, fn($value, $key) => [Str::camel($key) => $value]));
            }

            foreach (Schema::getIndexes($name) as $index)
            {
                @$this->migrationTables[$name]['indexes'][$index['name']] =
                    new ColumnDefinition(Arr::mapWithKeys($index, fn($value, $key) => [Str::camel($key) => $value]));
            }
        }
    }


    /**
     * Generate migration structures
     *
     * @return array[]
     */
    public function generateMigrationStructures()
    {
        $currentTable = $this->migrationTables ?? [];
        $tableStatus = $currentTable;

        $migrations = [];

        $dateIndex = time();
        $date = function () use(&$dateIndex) { return date('Y_m_d_His', $dateIndex++); };

        $marked = [];
        // Add new/changed structures
        foreach ($this->tables as $name => $table)
        {
            $migration = [
                'table' => $name,
                'command' => 'table',
                'before' => $tableStatus[$name] ?? null,

                'columns' => [
                    'added' => [],
                    'changed' => [],
                    'removed' => [],
                    'renamed' => [],
                ],
                'indexes' => [
                    'added' => [],
                    'changed' => [],
                    'removed' => [],
                    'renamed' => [],
                    'depended' => false,
                ],
            ];

            // Check new columns
            foreach ($table->getColumns() as $column)
            {
                $col = $column->name;

                // Find old name
                $oldNames = $column->get('oldNames', []);
                unset($column->oldNames);
                $hasOldName = false;
                $oldName = $col;
                foreach ($oldNames as $_name)
                {
                    if (isset($currentTable[$name]['columns'][$_name]))
                    {
                        $hasOldName = true;
                        $oldName = $_name;
                        break;
                    }
                }

                // Rename column
                if ($hasOldName)
                {
                    $migration['columns']['renamed'][$oldName] = $col;
                    @$tableStatus[$name]['columns'][$col] = $tableStatus[$name]['columns'][$oldName];
                    unset($tableStatus[$name]['columns'][$oldName]);
                }

                // Exists column -> Changed or nothing
                if (isset($currentTable[$name]['columns'][$oldName]))
                {
                    if ($this->checkColumnIsChanged($column, $currentTable[$name]['columns'][$oldName]))
                    {
                        $migration['columns']['changed'][$col] = $column;
                        @$tableStatus[$name]['columns'][$col] = $column;
                    }
                }
                // New column
                elseif (!$hasOldName)
                {
                    $migration['columns']['added'][$col] = $column;
                    @$tableStatus[$name]['columns'][$col] = $column;
                }

                @$marked[$name]['columns'][$oldName] = true;
            }

            // Check new commands
            foreach ($table->getCommands() as $command)
            {
                if ($index = $command->get('index'))
                {
                    // Exists index -> Changed or nothing
                    if (isset($currentTable[$name]['indexes'][$index]))
                    {
                        if ($this->checkColumnIsChanged($command, $currentTable[$name]['indexes'][$index]))
                        {
                            $migration['indexes']['changed'][$index] = $command;
                            @$tableStatus[$name]['indexes'][$index] = $command;

                            if ($on = $command->get('on'))
                            {
                                $migration['indexes']['depended'] = true;
                            }
                        }
                    }
                    // New index
                    else
                    {
                        $migration['indexes']['added'][$index] = $command;
                        @$tableStatus[$name]['indexes'][$index] = $command;

                        if ($on = $command->get('on'))
                        {
                            $migration['indexes']['depended'] = true;
                        }
                    }

                    @$marked[$name]['indexes'][$index] = true;
                }
            }

            $action = isset($currentTable[$name]) ? 'modify' : 'create';
            $migrations["{$date()}_{$action}_{$name}_table"] = $migration;
        }

        // Find removed columns & indexes
        foreach ($currentTable as $name => $table)
        {
            $removedColumns = [];
            $removedIndexes = [];
            foreach ($table['columns'] as $col => $column)
            {
                if (!isset($marked[$name]['columns'][$col]))
                {
                    $removedColumns[] = $col;
                }
            }
            foreach ($table['indexes'] as $ind => $index)
            {
                if (!isset($marked[$name]['indexes'][$ind]))
                {
                    $removedIndexes[] = $ind;
                }
            }

            // Table is not exists -> Drop table
            if (!isset($this->tables[$name]))
            {
                if ($this->includeDropTables && !in_array($name, ['password_reset_tokens', 'sessions']))
                {
                    $migrations["{$date()}_drop_{$name}_table"] = [
                        'table'   => $name,
                        'command' => 'drop',
                    ];
                }
            }
            // Drop columns & indexes
            else if ($removedColumns || $removedIndexes)
            {
                foreach (array_reverse(array_keys($migrations)) as $key)
                {
                    if ($migrations[$key]['table'] == $name)
                    {
                        array_push($migrations[$key]['columns']['removed'], ...$removedColumns);
                        array_push($migrations[$key]['indexes']['removed'], ...$removedIndexes);
                        break;
                    }
                }
            }
        }

        $insert = [];
        // Move indexes that have dependency, to best place
        foreach ($migrations as $name => $migrate)
        {
            if ($migrate['command'] == 'table' && $migrate['indexes']['depended'])
            {
                unset($migrate['indexes']['depended']);
                $migrations["{$date()}_add_{$migrate['table']}_indexes"] = [
                    'table' => $migrate['table'],
                    'command' => 'table',

                    'columns' => [
                        'added' => [],
                        'changed' => [],
                        'removed' => [],
                        'renamed' => [],
                    ],
                    'indexes' => [
                        'added' => $migrate['indexes']['added'],
                        'changed' => $migrate['indexes']['changed'],
                        'removed' => [],
                        'renamed' => [],
                    ],
                ];
                $insert["{$date()}_remove_{$migrate['table']}_indexes"] = [
                    'table' => $migrate['table'],
                    'command' => 'table',

                    'columns' => [
                        'added' => [],
                        'changed' => [],
                        'removed' => [],
                        'renamed' => [],
                    ],
                    'indexes' => [
                        'added' => [],
                        'changed' => [],
                        'removed' => $migrate['indexes']['removed'],
                        'renamed' => [],
                    ],
                ];
                $migrations[$name]['indexes'] = [
                    'added' => [],
                    'changed' => [],
                    'removed' => [],
                    'renamed' => [],
                ];
            }
        }
        $migrations = [...$insert, ...$migrations];

        // Delete empty migrations
        foreach ($migrations as $index => $migration)
        {
            if (
                $migration['command'] == 'table' &&
                !array_filter($migration['columns'], fn($x) => (bool) $x) &&
                !array_filter($migration['indexes'], fn($x) => (bool) $x)
            )
            {
                unset($migrations[$index]);
            }
        }

        return $migrations;
    }

    public function generateMigrationFiles()
    {
        return $this->generateMigrationFilesFrom(
            $this->generateMigrationStructures()
        );
    }

    public function generateMigrationStubs(array $files)
    {
        $stub = file_get_contents(__DIR__ . '/stubs/migration.stub');

        foreach ($files as $name => $value)
        {
            $up = implode("\n        ", $value['up']);
            $down = implode("\n        ", $value['down']);

            $files[$name] = str_replace(
                ['{{ up }}', '{{ down }}'],
                [$up, $down],
                $stub,
            );
        }

        return $files;
    }

    protected function generateMigrationFilesFrom(array $migrations)
    {
        $files = [];
        $createdTables = array_keys($this->migrationTables ?? []);

        foreach ($migrations as $name => $migration)
        {
            switch ($migration['command'])
            {
                case 'table':
                    $table = $migration['table'];
                    $isCreating = !in_array($table, $createdTables);
                    if ($isCreating) $createdTables[] = $table;
                    $files[$name] = $isCreating ? $this->makeMigrationCreate($migration) : $this->makeMigrationTable($migration);
                    break;

                case 'drop':
                    $files[$name] = $this->makeMigrationDrop($migration);
                    break;

                default:
                    die("Error");
            }
        }

        return $files;
    }

    protected function makeMigrationCreate(array $migration)
    {
        $inner = $this->makeMigrationTableInner($migration);

        return [
            'up' => [
                "Schema::create({$this->writeObject($migration['table'])}, function (Blueprint \$table) {",
                ...array_map(fn($code) => "    " . $code, $inner['up']),
                "});",
            ],
            'down' => [
                "Schema::drop({$this->writeObject($migration['table'])});",
            ],
        ];
    }

    protected function makeMigrationTable(array $migration)
    {
        $inner = $this->makeMigrationTableInner($migration);

        return [
            'up' => [
                "Schema::table({$this->writeObject($migration['table'])}, function (Blueprint \$table) {",
                ...array_map(fn($code) => "    " . $code, $inner['up']),
                "});",
            ],
            'down' => [
                "Schema::table({$this->writeObject($migration['table'])}, function (Blueprint \$table) {",
                ...array_map(fn($code) => "    " . $code, $inner['down']),
                "});",
            ],
        ];
    }

    protected function makeMigrationTableInner(array $migration)
    {
        $up = [];
        $down = [];

        foreach ($migration['columns']['removed'] as $column)
        {
            $up[] = "\$table->dropColumn({$this->writeObject($column)});";
            if (@$migration['before'])
            {
                $down[] = $this->writeColumn($migration['before']['columns'][$column]);
            }
        }

        foreach ($migration['columns']['renamed'] as $from => $to)
        {
            $up[] = "\$table->renameColumn({$this->writeObject($from)}, {$this->writeObject($to)});";
            if (@$migration['before'])
            {
                $down[] = "\$table->renameColumn({$this->writeObject($to)}, {$this->writeObject($from)});";
            }
        }

        foreach ($migration['columns']['added'] as $name => $column)
        {
            $up[] = $this->writeColumn($column);
            $down[] = "\$table->dropColumn({$this->writeObject($name)});";
        }

        foreach ($migration['columns']['changed'] as $name => $column)
        {
            $up[] = $this->writeColumn($column, true);
            if (@$migration['before'])
            {
                $down[] = $this->writeColumn($migration['before']['columns'][$name], true);
            }
        }

        // Index

        foreach ($migration['indexes']['removed'] as $index)
        {
            $up[] = "\$table->dropIndex({$this->writeObject($index)});";
            if (@$migration['before'])
            {
                $down[] = $this->writeCommand($migration['before']['indexes'][$index]);
            }
        }

        foreach ($migration['indexes']['renamed'] as $from => $to)
        {
            $up[] = "\$table->renameIndex({$this->writeObject($from)}, {$this->writeObject($to)});";
            if (@$migration['before'])
            {
                $down[] = "\$table->renameIndex({$this->writeObject($to)}, {$this->writeObject($from)});";
            }
        }

        foreach ($migration['indexes']['added'] as $name => $index)
        {
            $up[] = $this->writeCommand($index);
            $down[] = "\$table->dropIndex({$this->writeObject($name)});";
        }

        return [
            'up' => $up,
            'down' => array_reverse($down),
        ];
    }

    protected function writeColumn(Fluent $fluent, bool $change = false)
    {
        $code = "\$table->{$fluent->type}({$this->writeObject($fluent->name)})";

        foreach ($fluent->getAttributes() as $key => $value)
        {
            if ($key == 'type' || $key == 'name' || $key == 'change')
                continue;

            if ($value === false || $value === null)
                continue;

            if ($value === true)
                $code .= "->{$key}()";
            else
                $code .= "->{$key}({$this->writeObject($value)})";
        }

        if ($change)
        {
            $code .= "->change()";
        }

        return $code . ';';
    }

    protected function writeCommand(Fluent $fluent)
    {
        $code = "\$table->{$fluent->name}({$this->writeObject(count($fluent->columns) == 1 ? $fluent->columns[0] : $fluent->columns)}, {$this->writeObject($fluent->index)})";

        foreach ($fluent->getAttributes() as $key => $value)
        {
            if ($key == 'index' || $key == 'name' || $key == 'columns')
                continue;

            if ($value === false || $value === null)
                continue;

            if ($value === true)
                $code .= "->{$key}()";
            else
                $code .= "->{$key}({$this->writeObject($value)})";
        }

        return $code . ';';
    }

    protected function makeMigrationDrop(array $migration)
    {
        return [
            'up' => [
                "Schema::drop({$this->writeObject($migration['table'])});",
            ],
            'down' => [],
        ];
    }

    protected function writeObject($value)
    {
        switch (gettype($value))
        {
            case 'boolean':
                return $value ? "true" : "false";

            case 'integer':
            case 'double':
                return (string) $value;

            case 'string':
                return "'" . addslashes($value) . "'";

            case 'array':
                $items = [];
                $i = 0;
                foreach ($value as $key => $item)
                {
                    if ($key === $i)
                    {
                        $items[] = $this->writeObject($item);
                        $i++;
                    }
                    else
                    {
                        $items[] = $this->writeObject($key) . ' => ' . $this->writeObject($item);
                    }
                }

                return "[" . implode(', ', $items) . "]";

            case 'object':
                return "unserialize(" . $this->writeObject(serialize($value)) . ")";

            default:
                return "null";
        }
    }

    public function checkColumnIsChanged(Fluent $left, Fluent $right)
    {
        $filter = fn ($value) => $value !== null && $value !== false;
        $left = array_filter($left->getAttributes(), $filter);
        $right = array_filter($right->getAttributes(), $filter);

        asort($left);
        asort($right);

        return $left != $right;
    }

}