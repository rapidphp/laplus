<?php

namespace Rapid\Laplus\Present\Generate\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Rapid\Laplus\Present\Generate\Structure\ColumnListState;
use Rapid\Laplus\Present\Generate\Structure\DatabaseState;
use Rapid\Laplus\Present\Generate\Structure\IndexListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationState;

trait MigrationGenerates
{

    /**
     * The defined migration state
     *
     * @var DatabaseState
     */
    protected DatabaseState $previousState;

    /**
     * The current migration state.
     * Default is $definedState
     *
     * @var DatabaseState
     */
    protected DatabaseState $currentState;

    /**
     * New migrations
     *
     * @var MigrationListState
     */
    protected MigrationListState $newMigrations;

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
    public function generate(): MigrationListState
    {
        // Initialize variables
        $this->previousState = $this->resolvedState ?? new DatabaseState();
        $this->currentState = clone $this->previousState;
        $this->newMigrations = new MigrationListState();
        $this->marked = [];

        // Add travels
        $this->generateTravels();

        // Add new/changed structures
        $this->generateChanges();

        // Find removed columns & indexes
        $this->generateRemoves();

        // Move indexes that have dependency, to best place
        $this->generateDependedIndexes();

        // Delete empty migrations
        $this->newMigrations->forgetEmpty();

        return $this->newMigrations;
    }

    protected function generateChanges(): void
    {
        foreach ($this->blueprints as $tableName => $blueprint) {
            $migration = new MigrationState(
                fileName: '',
                table: $tableName,
                command: MigrationState::COMMAND_TABLE,
                before: $this->currentState->get($tableName),
            );

            // Check new columns
            $this->generateNewColumns($blueprint, $migration);

            // Check new commands
            $this->generateNewCommands($blueprint, $migration);

            // Choosing name
            if (!$this->previousState->get($tableName)) {
                $migration->forceName($this->nameOfCreateTable($tableName));
            } else {
                $migration->fileName = $this->nameOfModifyTable($tableName);
            }

            // Add to $newState
            $this->newMigrations->add($migration);
        }
    }

    protected function generateNewColumns(Blueprint $blueprint, MigrationState $migration): void
    {
        foreach ($blueprint->getColumns() as $column) {
            $columnName = $column->name;

            // Find old name
            $oldName = $this->findColumnOldName($migration->table, $column);
            $hasOldName = isset($oldName);
            $oldName ??= $columnName;
            unset($column->oldNames);

            // Rename column
            if ($hasOldName) {
                $this->generateRenameColumn($migration, $oldName, $columnName);
            }

            // Exists column -> Changed or nothing
            if (isset($this->previousState->get($migration->table)?->columns[$oldName])) {
                if ($changes = $this->findColumnChanges($column, $this->previousState->get($migration->table)->columns[$oldName])) {
                    $this->generateChangeColumn($migration, $column, $changes);
                }
            } // New column
            elseif (!$hasOldName) {
                $this->generateAddColumn($migration, $column);
            }

            @$this->marked[$migration->table]['columns'][$oldName] = true;
        }
    }

    protected function generateRenameColumn(MigrationState $migration, string $old, string $new): void
    {
        $migration->columns->renamed($old, $new);
        $this->currentState->getOrCreate($migration->table)->columns[$new] = $this->currentState->get($migration->table)->columns[$old];
        unset($this->currentState->get($migration->table)->columns[$old]);

        $migration->suggestName($new, $this->nameOfRenameColumn($old, $new, $migration->table));
    }

    protected function generateChangeColumn(MigrationState $migration, Fluent $column, array $changes): void
    {
        $migration->columns->changed($column->name, $column);
        $this->currentState->getOrCreate($migration->table)->columns[$column->name] = $column;

        $migration->suggestName($column->name, $this->nameOfModifyColumn($column->name, $changes, $migration->table), false);
    }

    protected function generateAddColumn(MigrationState $migration, Fluent $column): void
    {
        $migration->columns->added($column->name, $column);
        $this->currentState->getOrCreate($migration->table)->columns[$column->name] = $column;

        $migration->suggestName($column->name, $this->nameOfAddColumn($column->name, $migration->table));
    }

    protected function generateNewCommands(Blueprint $blueprint, MigrationState $migration): void
    {
        foreach ($blueprint->getCommands() as $command) {
            /** @var string $index */
            if ($index = $command->get('index')) {
                // Exists index -> Changed or nothing
                if (isset($this->previousState->get($migration->table)->indexes[$index])) {
                    if ($changes = $this->findColumnChanges($command, $this->previousState->get($migration->table)->indexes[$index])) {
                        $this->generateChangeIndex($migration, $index, $command, $changes);
                    }
                } // New index
                else {
                    $this->generateNewIndex($migration, $index, $command);
                }

                @$this->marked[$migration->table]['indexes'][$index] = true;
            }
        }
    }

    protected function generateChangeIndex(MigrationState $migration, string $index, Fluent $command, array $changes): void
    {
        $migration->indexes->changed($index, $command);
        $this->currentState->getOrCreate($migration->table)->indexes[$index] = $command;

        if ($on = $command->get('on')) {
            $migration->indexes->depended = true;
        }
    }

    protected function generateNewIndex(MigrationState $migration, string $index, Fluent $command): void
    {
        $migration->indexes->added($index, $command);
        $this->currentState->getOrCreate($migration->table)->indexes[$index] = $command;

        if ($on = $command->get('on')) {
            $migration->indexes->depended = true;
        }
    }

    protected function generateRemoves(): void
    {
        foreach ($this->previousState->tables as $name => $table) {
            $removedColumns = [];
            $removedIndexes = [];
            foreach ($table->columns as $columnName => $column) {
                if (!isset($this->marked[$name]['columns'][$columnName])) {
                    $removedColumns[] = $columnName;
                }
            }
            foreach ($table->indexes as $indexName => $index) {
                if (!isset($this->marked[$name]['indexes'][$indexName])) {
                    $removedIndexes[] = $indexName;
                }
            }

            // Table is not exists -> Drop table
            if (!isset($this->blueprints[$name])) {
                if ($this->includeDropTables && !in_array($name, ['password_reset_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'])) {
                    $this->newMigrations->add(
                        new MigrationState(
                            fileName: $this->nameOfDropTable($name),
                            table: $name,
                            command: MigrationState::COMMAND_DROP,
                        ),
                    );
                }
            } // Drop columns & indexes
            else if ($removedColumns || $removedIndexes) {
                foreach (array_reverse($this->newMigrations->all) as $migration) {
                    if ($migration->table == $name) {
                        array_push($migration->columns->removed, ...$removedColumns);
                        array_push($migration->indexes->removed, ...$removedIndexes);

                        if (count($removedColumns) == 1) {
                            $migration->suggestName($removedColumns[0], $this->nameOfRemoveColumn($removedColumns[0], $migration->table));
                        }

                        break;
                    }
                }
            }
        }
    }

    protected function generateDependedIndexes(): void
    {
        $insert = new MigrationListState();

        foreach ($this->newMigrations->all as $migration) {
            if ($migration->command == MigrationState::COMMAND_TABLE && $migration->indexes->depended) {
                $migration->indexes->depended = false;
                $this->newMigrations->add(
                    new MigrationState(
                        fileName: $this->nameOfAddIndexes($migration->table),
                        table: $migration->table,
                        command: MigrationState::COMMAND_TABLE,
                        indexes: new IndexListState(
                            added: $migration->indexes->added,
                            changed: $migration->indexes->changed,
                        ),
                        isLazy: true,
                    ),
                );
                $insert->add(
                    new MigrationState(
                        fileName: $this->nameOfRemoveIndexes($migration->table),
                        table: $migration->table,
                        command: MigrationState::COMMAND_TABLE,
                        indexes: new IndexListState(
                            removed: $migration->indexes->removed,
                        ),
                        isLazy: true,
                    ),
                );

                $migration->indexes = new IndexListState();
            }
        }

        $this->newMigrations = new MigrationListState([...$insert->all, ...$this->newMigrations->all]);
    }

    protected function generateTravels(): void
    {
        $softRemoved = [];
        $added = [];
        $renamed = [];

        foreach ($this->discoveredTravels as $relativePath => $travel) {
            if (isset($this->resolvedTravels) && in_array($relativePath, $this->resolvedTravels)) {
                continue;
            }

            $tables = $travel->getTables();

            if (!$travel->anywayBefore && !$travel->anywayFinally) {
                if (!$tables) {
                    continue;
                }

                foreach ($this->getTravelColumns((array)$travel->whenRemoving, reset($tables)) as [$table, $column]) {
                    if (in_array("$table.$column", $softRemoved)) {
                        continue;
                    }

                    $trashedColumn = $travel->trashed($column);

                    if (
                        !($newState = $this->getBlueprintOrNull($table)) ||
                        !($previousState = $this->previousState->get($table))
                    ) {
                        throw new \Exception("Travel [$relativePath] depended on [$table] table that not exists!");
                    }

                    if (!isset($previousState->columns[$column])) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$column] is removing, but it doesn't exists!");
                    }

                    if (isset($newState->getColumns()[$column])) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$column] is removing, but it's already exists!");
                    }

                    if (isset($newState->getColumns()[$trashedColumn]) || isset($previousState->columns[$trashedColumn])) {
                        throw new \Exception("Travel [$relativePath] needs to save the removed column, but the [$trashedColumn] is reserved!");
                    }

                    $this->newMigrations->add(
                        new MigrationState(
                            fileName: $this->nameOfSoftRemoveColumn($column, $table),
                            table: $table,
                            command: MigrationState::COMMAND_TABLE,
                            columns: new ColumnListState(
                                renamed: [$column => $trashedColumn],
                            ),
                        ),
                    );

                    $softRemoved[] = "$table.$column";
                }

                foreach ($this->getTravelColumns((array)$travel->whenAdded, reset($tables)) as [$table, $column]) {
                    if (in_array("$table.$column", $added)) {
                        continue;
                    }

                    if (
                        !($newState = $this->getBlueprintOrNull($table)) ||
                        !($previousState = $this->previousState->get($table))
                    ) {
                        throw new \Exception("Travel [$relativePath] depended on [$table] table that not exists!");
                    }

                    if (isset($previousState->columns[$column])) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$column] is added, but it's already exists!");
                    }

                    if (!isset($newState->getColumns()[$column])) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$column] is added, but it doesn't exists!");
                    }

                    $this->newMigrations->add(
                        new MigrationState(
                            fileName: $this->nameOfAddColumn($column, $table),
                            table: $table,
                            command: MigrationState::COMMAND_TABLE,
                            columns: new ColumnListState(
                                added: [
                                    $column => $newState->getColumns()[$column],
                                ],
                            ),
                        ),
                    );

                    $added[] = "$table.$column";
                }

                foreach ($this->getTravelRenameColumns((array)$travel->whenRenamed, reset($tables)) as [$table, $from, $to]) {
                    if (in_array("$table.$from.$to", $renamed)) {
                        continue;
                    }

                    if (
                        !($newState = $this->getBlueprintOrNull($table)) ||
                        !($previousState = $this->previousState->get($table))
                    ) {
                        throw new \Exception("Travel [$relativePath] depended on [$table] table that not exists!");
                    }

                    if (!isset($previousState->columns[$from])) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$from] is renamed, but it doesn't exists!");
                    }

                    if (!isset($newState->getColumns()[$to])) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$from] is renamed to [$table.$to], but it's already exists!");
                    }

                    if ($this->findColumnOldName($table, $newState->getColumns()[$to]) === $from) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$from] is renamed to [$table.$to], but it doesn't renamed!");
                    }

                    $this->newMigrations->add(
                        new MigrationState(
                            fileName: $this->nameOfRenameColumn($from, $to, $table),
                            table: $table,
                            command: MigrationState::COMMAND_TABLE,
                            columns: new ColumnListState(
                                renamed: [
                                    $from => $to,
                                ],
                            ),
                        ),
                    );

                    $renamed[] = "$table.$from.$to";
                }
            }

            $this->newMigrations->add(
                new MigrationState(
                    fileName: $this->nameOfTravel($relativePath),
                    table: $tables ? reset($tables) : '',
                    command: MigrationState::COMMAND_TABLE,
                    isLazy: $travel->anywayFinally,
                    travel: $relativePath,
                ),
            );
        }
    }

    protected function getTravelColumns(array $columns, string $defaultTable): array
    {
        return Arr::map($columns, function ($column) use ($defaultTable) {
            return $this->getTravelColumnName($column, $defaultTable);
        });
    }

    protected function getTravelRenameColumns(array $columns, string $defaultTable): array
    {
        return Arr::map($columns, function ($value, $key) use ($defaultTable) {
            [$fromTable, $fromColumn] = $this->getTravelColumnName($key, null);
            [$toTable, $toColumn] = $this->getTravelColumnName($key, null);

            return [$toTable ?? $fromTable ?? $defaultTable, $fromColumn, $toColumn];
        });
    }

    protected function getTravelColumnName(string $column, string $defaultTable): array
    {
        if (str_contains($column, '.')) {
            [$table, $column] = explode('.', $column, 2);
            if (str_contains($table, '\\')) {
                $table = app($table)->getTable();
            }

            return [$table, $column];
        } else {
            return [$defaultTable, $column];
        }
    }

}