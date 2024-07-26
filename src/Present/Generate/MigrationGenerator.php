<?php

namespace Rapid\Laplus\Present\Generate;

use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Rapid\Laplus\Present\Generate\Structure\ColumnListState;
use Rapid\Laplus\Present\Generate\Structure\DefinedMigrationState;
use Rapid\Laplus\Present\Generate\Structure\IndexListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationFileListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationFileState;
use Rapid\Laplus\Present\Generate\Structure\MigrationListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationState;
use Rapid\Laplus\Present\Present;

class MigrationGenerator
{
    use Concerns\Resolves,
        Concerns\MigrationStubs;

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


    protected DefinedMigrationState $definedMigrationState;


    /**
     * Generate migration structures
     *
     * @return MigrationListState
     */
    public function generateMigrationStructures()
    {
        $currentTable = $this->definedMigrationState ?? new DefinedMigrationState();
        $tableStatus = clone $currentTable;

        $migrations = new MigrationListState();

        $dateIndex = time();
        $date = function () use(&$dateIndex) { return date('Y_m_d_His', $dateIndex++); };

        $marked = [];
        // Add new/changed structures
        foreach ($this->tables as $tableName => $table)
        {
            $migration = new MigrationState(
                fileName: '',
                table: $tableName,
                command: 'table',
                before: $tableStatus->get($tableName),

                columns: new ColumnListState(),
                indexes: new IndexListState(),
            );
            $suggestedName = [];

            // Check new columns
            foreach ($table->getColumns() as $column)
            {
                $columnName = $column->name;

                // Find old name
                $oldNames = $column->get('oldNames', []);
                unset($column->oldNames);
                $hasOldName = false;
                $oldName = $columnName;
                foreach ($oldNames as $_name)
                {
                    if (isset($currentTable->get($tableName)->columns[$_name]))
                    {
                        $hasOldName = true;
                        $oldName = $_name;
                        break;
                    }
                }

                $currentSuggestedName = '';

                // Rename column
                if ($hasOldName)
                {
                    $migration->columns->renamed($oldName, $columnName);
                    $tableStatus->getOrCreate($tableName)->columns[$columnName] = $tableStatus->get($tableName)->columns[$oldName];
                    unset($tableStatus->get($tableName)->columns[$oldName]);

                    $currentSuggestedName = "rename_{$oldName}_to_{$columnName}_in_{$tableName}_table";
                }

                // Exists column -> Changed or nothing
                if (isset($currentTable->get($tableName)?->columns[$oldName]))
                {
                    if ($this->checkColumnIsChanged($column, $currentTable->get($tableName)->columns[$oldName]))
                    {
                        $migration->columns->changed($columnName, $column);
                        $tableStatus->getOrCreate($tableName)->columns[$columnName] = $column;

                        if (!$currentSuggestedName)
                        {
                            $whatChanged = $this->getNameForWhatChangedInColumn(
                                $column,
                                $currentTable->get($tableName)->columns[$oldName]
                            );
                            if ($whatChanged)
                                $currentSuggestedName = "change_{$columnName}_{$whatChanged}_in_{$tableName}_table";
                            else
                                $currentSuggestedName = "change_{$columnName}_in_{$tableName}_table";
                        }
                    }
                }
                // New column
                elseif (!$hasOldName)
                {
                    $migration->columns->added($columnName, $column);
                    $tableStatus->getOrCreate($tableName)->columns[$columnName] = $column;

                    $currentSuggestedName = "add_{$columnName}_to_{$tableName}";
                }

                @$marked[$tableName]['columns'][$oldName] = true;

                if ($currentSuggestedName)
                    $suggestedName[] = $currentSuggestedName;
            }

            // Check new commands
            foreach ($table->getCommands() as $command)
            {
                if ($index = $command->get('index'))
                {
                    // Exists index -> Changed or nothing
                    if (isset($currentTable->get($tableName)->indexes[$index]))
                    {
                        if ($this->checkColumnIsChanged($command, $currentTable->get($tableName)->indexes[$index]))
                        {
                            $migration->indexes->changed($index, $command);
                            $tableStatus->getOrCreate($tableName)->indexes[$index] = $command;

                            if ($on = $command->get('on'))
                            {
                                $migration->indexes->depended = true;
                            }
                        }
                    }
                    // New index
                    else
                    {
                        $migration->indexes->added($index, $command);
                        $tableStatus->getOrCreate($tableName)->indexes[$index] = $command;

                        if ($on = $command->get('on'))
                        {
                            $migration->indexes->depended = true;
                        }
                    }

                    @$marked[$tableName]['indexes'][$index] = true;
                }
            }

            if (count($suggestedName) == 1)
            {
                $selectedName = $suggestedName[0];
            }
            else
            {
                $selectedName = ($currentTable->get($tableName) ? 'modify' : 'create') . "_{$tableName}_table";
            }
            $migration->fileName = "{$date()}_{$selectedName}";
            $migrations->add($migration);
        }

        // Find removed columns & indexes
        foreach ($currentTable->tables as $name => $table)
        {
            $removedColumns = [];
            $removedIndexes = [];
            foreach ($table->columns as $columnName => $column)
            {
                if (!isset($marked[$name]['columns'][$columnName]))
                {
                    $removedColumns[] = $columnName;
                }
            }
            foreach ($table->indexes as $indexName => $index)
            {
                if (!isset($marked[$name]['indexes'][$indexName]))
                {
                    $removedIndexes[] = $indexName;
                }
            }

            // Table is not exists -> Drop table
            if (!isset($this->tables[$name]))
            {
                if ($this->includeDropTables && !in_array($name, ['password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs']))
                {
                    $migrations->add(
                        new MigrationState(
                            fileName: "{$date()}_drop_{$name}_table",
                            table: $name,
                            command: 'drop',
                        )
                    );
                }
            }
            // Drop columns & indexes
            else if ($removedColumns || $removedIndexes)
            {
                foreach (array_reverse(array_keys($migrations->all)) as $key)
                {
                    if ($migrations->all[$key]->table == $name)
                    {
                        array_push($migrations->all[$key]->columns->removed, ...$removedColumns);
                        array_push($migrations->all[$key]->indexes->removed, ...$removedIndexes);
                        break;
                    }
                }
            }
        }

        $insert = new MigrationListState();
        // Move indexes that have dependency, to best place
        foreach ($migrations->all as $name => $migrate)
        {
            if ($migrate->command == 'table' && $migrate->indexes->depended)
            {
                $migrate->indexes->depended = false;
                $migrations->add(
                    new MigrationState(
                        fileName: "{$date()}_add_{$migrate->table}_indexes",
                        table: $migrate->table,
                        command: 'table',

                        indexes: new IndexListState(
                            added: $migrate->indexes->added,
                            changed: $migrate->indexes->changed,
                        ),
                    )
                );
                $insert->add(
                    new MigrationState(
                        fileName: "{$date()}_remove_{$migrate->table}_indexes",
                        table: $migrate->table,
                        command: 'table',
                        indexes: new IndexListState(
                            removed: $migrate->indexes->removed,
                        )
                    ),
                );

                $migrate->indexes = new IndexListState();
            }
        }
        $migrations = new MigrationListState([...$insert->all, ...$migrations->all]);

        // Delete empty migrations
        foreach ($migrations->all as $index => $migration)
        {
            if (
                $migration->command == 'table' &&
                $migration->columns->isEmpty() &&
                $migration->indexes->isEmpty()
            )
            {
                unset($migrations->all[$index]);
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

    public function generateMigrationStubs(MigrationFileListState $files)
    {
        $stub = file_get_contents(__DIR__ . '/../../Commands/stubs/migration.stub');

        $result = [];
        foreach ($files->files as $name => $file)
        {
            $up = implode("\n        ", $file->up);
            $down = implode("\n        ", $file->down);

            $result[$name] = str_replace(
                ['{{ up }}', '{{ down }}'],
                [$up, $down],
                $stub,
            );
        }

        return $result;
    }

    protected function generateMigrationFilesFrom(MigrationListState $migrations)
    {
        $files = new MigrationFileListState();
        $createdTables = isset($this->definedMigrationState) ? array_keys($this->definedMigrationState->tables) : [];

        foreach ($migrations->all as $name => $migration)
        {
            switch ($migration->command)
            {
                case 'table':
                    $tableName = $migration->table;
                    $isCreating = !in_array($tableName, $createdTables);
                    if ($isCreating) $createdTables[] = $tableName;
                    $files->files[$name] = $isCreating ?
                        $this->makeMigrationCreate($migration) :
                        $this->makeMigrationTable($migration);
                    break;

                case 'drop':
                    $files->files[$name] = $this->makeMigrationDrop($migration);
                    break;

                default:
                    die("Error"); // TODO : Should change
            }
        }

        return $files;
    }

    public function checkColumnIsChanged(Fluent $left, Fluent $right)
    {
        $filter = fn ($value) => $value !== null && $value !== false;
        $left = array_filter($left->getAttributes(), $filter);
        $right = array_filter($right->getAttributes(), $filter);

        Arr::forget($left, ['change']);
        Arr::forget($right, ['change']);

        asort($left);
        asort($right);

        return $left != $right;
    }

    public function getWhatChangedInColumn(Fluent $left, Fluent $right)
    {
        $leftAttributes = $left->getAttributes();
        $rightAttributes = $right->getAttributes();

        return array_diff(
            array_keys(
            array_diff_assoc($leftAttributes, $rightAttributes) + array_diff_assoc($rightAttributes, $leftAttributes),
            ),
            ['change'],
        );
    }

    public function getNameForWhatChangedInColumn(Fluent $left, Fluent $right)
    {
        $diff = $this->getWhatChangedInColumn($left, $right);

        if (count($diff) == 1)
        {
            return $diff[0];
        }

        if (count($diff) == 2)
        {
            return implode("_and_", $diff);
        }

        return null;
    }

}
