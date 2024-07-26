<?php

namespace Rapid\Laplus\Present\Generate\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Fluent;
use Rapid\Laplus\Present\Generate\Structure\DefinedMigrationState;
use Rapid\Laplus\Present\Generate\Structure\IndexListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationState;

trait MigrationGenerates
{

    /**
     * The defined migration state
     *
     * @var DefinedMigrationState
     */
    protected DefinedMigrationState $definedState;

    /**
     * The current migration state.
     * Default is $definedState
     *
     * @var DefinedMigrationState
     */
    protected DefinedMigrationState $currentState;

    /**
     * New migrations
     *
     * @var MigrationListState
     */
    protected MigrationListState $newState;

    /**
     * Tables and columns that marked as exists
     *
     * @var array
     */
    protected array $marked;

    /**
     * Generate migration structures
     *
     * @return MigrationListState
     */
    public function generate()
    {
        // Initialize variables
        $this->definedState = $this->definedMigrationState ?? new DefinedMigrationState();
        $this->currentState = clone $this->definedState;
        $this->newState = new MigrationListState();
        $this->marked = [];

        // Add new/changed structures
        $this->generateChanges();

        // Find removed columns & indexes
        $this->generateRemoves();

        // Move indexes that have dependency, to best place
        $this->generateDependedIndexes();

        // Delete empty migrations
        $this->newState->forgetEmpty();

        return $this->newState;
    }


    protected function generateChanges()
    {
        foreach ($this->tables as $tableName => $table)
        {
            $migration = new MigrationState(
                fileName: '',
                table: $tableName,
                command: 'table',
                before: $this->currentState->get($tableName),
            );

            // Check new columns
            $this->generateNewColumns($table, $migration);

            // Check new commands
            $this->generateNewCommands($table, $migration);

            // Choosing name
            if (!$this->definedState->get($tableName))
            {
                $migration->forceName($this->nameOfCreateTable($tableName));
            }
            else
            {
                $migration->fileName = $this->nameOfModifyTable($tableName);
            }

            // Add to $newState
            $this->newState->add($migration);
        }
    }

    protected function generateRemoves()
    {
        foreach ($this->definedState->tables as $name => $table)
        {
            $removedColumns = [];
            $removedIndexes = [];
            foreach ($table->columns as $columnName => $column)
            {
                if (!isset($this->marked[$name]['columns'][$columnName]))
                {
                    $removedColumns[] = $columnName;
                }
            }
            foreach ($table->indexes as $indexName => $index)
            {
                if (!isset($this->marked[$name]['indexes'][$indexName]))
                {
                    $removedIndexes[] = $indexName;
                }
            }

            // Table is not exists -> Drop table
            if (!isset($this->tables[$name]))
            {
                if ($this->includeDropTables && !in_array($name, ['password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs']))
                {
                    $this->newState->add(
                        new MigrationState(
                            fileName: $this->nameOfDropTable($name),
                            table: $name,
                            command: 'drop',
                        )
                    );
                }
            }
            // Drop columns & indexes
            else if ($removedColumns || $removedIndexes)
            {
                foreach (array_reverse($this->newState->all) as $migration)
                {
                    if ($migration->table == $name)
                    {
                        array_push($migration->columns->removed, ...$removedColumns);
                        array_push($migration->indexes->removed, ...$removedIndexes);

                        if (count($removedColumns) == 1)
                        {
                            $migration->suggestName($removedColumns[0], $this->nameOfRemoveColumn($removedColumns[0], $migration->table));
                        }

                        break;
                    }
                }
            }
        }
    }

    protected function generateDependedIndexes()
    {
        $insert = new MigrationListState();

        foreach ($this->newState->all as $migration)
        {
            if ($migration->command == 'table' && $migration->indexes->depended)
            {
                $migration->indexes->depended = false;
                $this->newState->add(
                    new MigrationState(
                        fileName: $this->nameOfAddIndexes($migration->table),
                        table: $migration->table,
                        command: 'table',

                        indexes: new IndexListState(
                            added: $migration->indexes->added,
                            changed: $migration->indexes->changed,
                        ),
                    )
                );
                $insert->add(
                    new MigrationState(
                        fileName: $this->nameOfRemoveIndexes($migration->table),
                        table: $migration->table,
                        command: 'table',
                        indexes: new IndexListState(
                            removed: $migration->indexes->removed,
                        )
                    ),
                );

                $migration->indexes = new IndexListState();
            }
        }

        $this->newState = new MigrationListState([...$insert->all, ...$this->newState->all]);
    }


    protected function generateNewColumns(Blueprint $table, MigrationState $migration)
    {
        foreach ($table->getColumns() as $column)
        {
            $columnName = $column->name;

            // Find old name
            $oldName = $this->findColumnOldName($migration->table, $column);
            $hasOldName = isset($oldName);
            $oldName ??= $columnName;
            unset($column->oldNames);

            // Rename column
            if ($hasOldName)
            {
                $this->generateRenameColumn($migration, $oldName, $columnName);
            }

            // Exists column -> Changed or nothing
            if (isset($this->definedState->get($migration->table)?->columns[$oldName]))
            {
                if ($changes = $this->findColumnChanges($column, $this->definedState->get($migration->table)->columns[$oldName]))
                {
                    $this->generateChangeColumn($migration, $column, $changes);
                }
            }
            // New column
            elseif (!$hasOldName)
            {
                $this->generateAddColumn($migration, $column);
            }

            @$this->marked[$migration->table]['columns'][$oldName] = true;
        }
    }

    protected function generateRenameColumn(MigrationState $migration, string $old, string $new)
    {
        $migration->columns->renamed($old, $new);
        $this->currentState->getOrCreate($migration->table)->columns[$new] = $this->currentState->get($migration->table)->columns[$old];
        unset($this->currentState->get($migration->table)->columns[$old]);

        $migration->suggestName($new, $this->nameOfRenameColumn($old, $new, $migration->table));
    }

    protected function generateChangeColumn(MigrationState $migration, Fluent $column, array $changes)
    {
        $migration->columns->changed($column->name, $column);
        $this->currentState->getOrCreate($migration->table)->columns[$column->name] = $column;

        $migration->suggestName($column->name, $this->nameOfModifyColumn($column->name, $changes, $migration->table), false);
    }

    protected function generateAddColumn(MigrationState $migration, Fluent $column)
    {
        $migration->columns->added($column->name, $column);
        $this->currentState->getOrCreate($migration->table)->columns[$column->name] = $column;

        $migration->suggestName($column->name, $this->nameOfAddColumn($column->name, $migration->table));
    }


    protected function generateNewCommands(Blueprint $table, MigrationState $migration)
    {
        foreach ($table->getCommands() as $command)
        {
            /** @var string $index */
            if ($index = $command->get('index'))
            {
                // Exists index -> Changed or nothing
                if (isset($this->definedState->get($migration->table)->indexes[$index]))
                {
                    if ($changes = $this->findColumnChanges($command, $this->definedState->get($migration->table)->indexes[$index]))
                    {
                        $this->generateChangeIndex($migration, $index, $command, $changes);
                    }
                }
                // New index
                else
                {
                    $this->generateNewIndex($migration, $index, $command);
                }

                @$this->marked[$migration->table]['indexes'][$index] = true;
            }
        }
    }

    protected function generateChangeIndex(MigrationState $migration, string $index, Fluent $command, array $changes)
    {
        $migration->indexes->changed($index, $command);
        $this->currentState->getOrCreate($migration->table)->indexes[$index] = $command;

        if ($on = $command->get('on'))
        {
            $migration->indexes->depended = true;
        }
    }

    protected function generateNewIndex(MigrationState $migration, string $index, Fluent $command)
    {
        $migration->indexes->added($index, $command);
        $this->currentState->getOrCreate($migration->table)->indexes[$index] = $command;

        if ($on = $command->get('on'))
        {
            $migration->indexes->depended = true;
        }
    }

}