<?php

namespace Rapid\Laplus\Present\Generate\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Fluent;
use Rapid\Laplus\Present\Generate\Structure\ColumnListState;
use Rapid\Laplus\Present\Generate\Structure\DatabaseState;
use Rapid\Laplus\Present\Generate\Structure\IndexListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationState;
use Rapid\Laplus\Present\Generate\Structure\NameSuggestion;
use Rapid\Laplus\Present\Generate\Structure\TableState;

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
     * The state that should migrate to it.
     *
     * @var DatabaseState
     */
    protected DatabaseState $outlookState;

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
     * The generator has been already generated
     *
     * @var bool
     */
    protected bool $alreadyGenerated = false;

    /**
     * Generate migration structures
     *
     * @return MigrationListState
     */
    public function generate(): MigrationListState
    {
        if ($this->alreadyGenerated) {
            throw new \RuntimeException('Migrations already generated.');
        }

        $this->alreadyGenerated = true;

        // Initialize variables
        $this->previousState = $this->resolvedState ?? new DatabaseState();
        $this->currentState = clone $this->previousState;
        $this->newMigrations = new MigrationListState();
        $this->marked = [];
        $this->defineOutlook();

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

    protected function defineOutlook(): void
    {
        if (isset($this->outlookState)) {
            return;
        }

        $this->outlookState = new DatabaseState();

        foreach ($this->blueprints as $tableName => $blueprint) {
            $table = new TableState();

            foreach ($blueprint->getColumns() as $column) {
                $table->columns[$column->name] = $column;
            }

            foreach ($blueprint->getCommands() as $command) {
                if ($command->index) {
                    $table->indexes[$command->index] = $command;
                }
            }

            $this->outlookState->put($tableName, $table);
        }
    }

    protected function generateChanges(): void
    {
        foreach ($this->outlookState->tables as $tableName => $table) {
            $this->generateTable($tableName, $table);
        }
    }

    protected function generateTable(string $tableName, TableState $table): void
    {
        $migration = new MigrationState(
            fileName: '',
            table: $tableName,
            command: MigrationState::COMMAND_TABLE,
        );

        $exists = (bool)$this->currentState->get($tableName);

        if (!$exists) {
            $this->currentState->put($tableName, new TableState());
        }

        // Check new columns
        $separatedRenames = $this->generateRenamesIfRequiredToSeparate($table, $migration);

        // Check new columns
        $this->generateNewColumns($table, $migration, $separatedRenames);

        // Check new commands
        $this->generateNewCommands($table, $migration);

        // Choosing name
        if (!$exists) {
            $migration->forceName($this->nameOfCreateTable($tableName));
        } else {
            $migration->fileName = $this->nameOfModifyTable($tableName);
        }

        $this->newMigrations->add($migration);
    }

    protected function generateRenamesIfRequiredToSeparate(TableState $table, MigrationState $migration): bool
    {
        $shouldSeparateRenamed = false;

        foreach ($table->columns as $columnName => $column) {
            // Find old name
            $oldName = $this->findColumnOldName($migration->table, $column);

            if (isset($oldName) && $this->findColumnChanges($column, $this->currentState->get($migration->table)->columns[$oldName])) {
                $shouldSeparateRenamed = true;
                break;
            }
        }

        if (!$shouldSeparateRenamed) {
            return false;
        }

        $renameMigration = new MigrationState(
            fileName: $this->nameOfRenameColumns($migration->table),
            table: $migration->table,
            command: MigrationState::COMMAND_TABLE,
        );

        foreach ($table->columns as $columnName => $column) {
            // Find old name
            $oldName = $this->findColumnOldName($migration->table, $column);
            $hasOldName = isset($oldName);

            // Rename column
            if ($hasOldName) {
                $this->generateRenameColumn($renameMigration, $oldName, $columnName);
            }

            @$this->marked[$migration->table]['columns'][$oldName] = true;
        }

        $this->newMigrations->add($renameMigration);
        return true;
    }

    protected function generateNewColumns(TableState $table, MigrationState $migration, bool $separatedRenames): void
    {
        foreach ($table->columns as $columnName => $column) {
            // Find old name
            $oldName = $this->findColumnOldName($migration->table, $column);
            $hasOldName = isset($oldName);
            $oldName ??= $columnName;
            unset($column->oldNames);

            // Rename column
            if (!$separatedRenames && $hasOldName) {
                $this->generateRenameColumn($migration, $oldName, $columnName);
            } // Exists column -> Changed or nothing
            elseif (isset($this->currentState->get($migration->table)?->columns[$oldName])) {
                $oldColumn = $this->currentState->get($migration->table)?->columns[$oldName];

                if ($changes = $this->findColumnChanges($column, $oldColumn)) {
                    $this->generateChangeColumn($migration, $column, $oldColumn, $changes);
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
        $migration->suggestion->addRename($new, $this->nameOfRenameColumn($old, $new, $migration->table));

        $this->currentState->get($migration->table)->renameColumn($old, $new);
        $this->marked[$migration->table]['columns'][$old] = true;
        $this->marked[$migration->table]['columns'][$new] = true;
    }

    protected function generateChangeColumn(MigrationState $migration, Fluent $column, Fluent $oldColumn, array $changes): void
    {
        $migration->columns->changed($column->name, $oldColumn, $column);

        if (!$migration->suggestion->has($column->name)) {
            $migration->suggestion->addChange($column->name, $this->nameOfModifyColumn($column->name, $changes, $migration->table));
        }

        $this->currentState->get($migration->table)->putColumn($column->name, $column);
    }

    protected function generateAddColumn(MigrationState $migration, Fluent $column): void
    {
        $migration->columns->added($column->name, $column);
        $migration->suggestion->addAdd($column->name, $this->nameOfAddColumn($column->name, $migration->table));

        $this->currentState->get($migration->table)->putColumn($column->name, $column);
    }

    protected function generateNewCommands(TableState $table, MigrationState $migration): void
    {
        foreach ($table->indexes as $index => $command) {
            // Exists index -> Changed or nothing
            if (isset($this->currentState->get($migration->table)->indexes[$index])) {
                $oldCommand = $this->currentState->get($migration->table)->indexes[$index];

                if ($changes = $this->findColumnChanges($command, $oldCommand)) {
                    $this->generateChangeIndex($migration, $index, $command, $oldCommand, $changes);
                }
            } // New index
            else {
                $this->generateNewIndex($migration, $index, $command);
            }

            @$this->marked[$migration->table]['indexes'][$index] = true;
        }
    }

    protected function generateChangeIndex(MigrationState $migration, string $index, Fluent $command, Fluent $oldCommand, array $changes): void
    {
        $migration->indexes->changed($index, $oldCommand, $command);

        if ($on = $command->get('on')) {
            $migration->indexes->depended = true;
        }

        $this->currentState->get($migration->table)->putIndex($index, $command);
    }

    protected function generateNewIndex(MigrationState $migration, string $index, Fluent $command): void
    {
        $migration->indexes->added($index, $command);

        if ($on = $command->get('on')) {
            $migration->indexes->depended = true;
        }

        $this->currentState->get($migration->table)->putIndex($index, $command);
    }

    protected function generateRemoves(): void
    {
        foreach ($this->currentState->tables as $name => $table) {
            $removedColumns = [];
            $removedIndexes = [];
            foreach ($table->columns as $columnName => $column) {
                if (!isset($this->marked[$name]['columns'][$columnName])) {
                    $removedColumns[] = [$columnName, $column];
                }
            }
            foreach ($table->indexes as $indexName => $index) {
                if (!isset($this->marked[$name]['indexes'][$indexName])) {
                    $removedIndexes[] = [$indexName, $index];
                }
            }

            // Table is not exists -> Drop table
            if (!$this->outlookState->get($name)) {
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
                            $migration->suggestion->addRemove($removedColumns[0][0], $this->nameOfRemoveColumn($removedColumns[0][0], $migration->table));
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

            if (!$travel->anywayBefore && !$travel->anywayFinally) {
                $tables = $travel->getTables();

                if (!$tables) {
                    continue;
                }

                /** @var MigrationState[] $prepares */
                $prepares = Arr::mapWithKeys($tables, fn($table) => [
                    $table => new MigrationState(
                        fileName: $this->nameOfTravelPrepare($table),
                        table: $table,
                        command: MigrationState::COMMAND_TABLE,
                    ),
                ]);

                foreach ($tables as $table) {
                    if (
                        !$this->currentState->get($table) &&
                        $newState = $this->outlookState->get($table)
                    ) {
                        $this->generateTable($table, $newState);
                    }
                }

                foreach ($this->getTravelColumns((array)$travel->whenRemoving, reset($tables)) as [$table, $column]) {
                    if (in_array("$table.$column", $softRemoved)) {
                        continue;
                    }

                    $trashedColumn = $travel->trashed($column);

                    if (
                        !($newState = $this->outlookState->get($table)) ||
                        !($previousState = $this->currentState->get($table))
                    ) {
                        throw new \Exception("Travel [$relativePath] depended on [$table] table that not exists!");
                    }

                    if (!$previousState->hasColumn($column)) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$column] is removing, but it doesn't exists!");
                    }

                    if ($newState->hasColumn($column)) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$column] is removing, but it's already exists!");
                    }

                    if ($newState->hasColumn($trashedColumn) || $previousState->hasColumn($trashedColumn)) {
                        throw new \Exception("Travel [$relativePath] needs to save the removed column, but the [$trashedColumn] is reserved!");
                    }

                    $prepares[$table]->columns->renamed($column, $trashedColumn);
                    $prepares[$table]->suggestion->addSoftRemove($column, $this->nameOfSoftRemoveColumn($column, $table));

                    $softRemoved[] = "$table.$column";
                    $this->currentState->get($table)->renameColumn($column, $trashedColumn);
                }

                foreach ($this->getTravelColumns((array)$travel->whenAdded, reset($tables)) as [$table, $column]) {
                    if (in_array("$table.$column", $added)) {
                        continue;
                    }

                    if (
                        !($newState = $this->outlookState->get($table)) ||
                        !($previousState = $this->currentState->get($table))
                    ) {
                        throw new \Exception("Travel [$relativePath] depended on [$table] table that not exists!");
                    }

                    if ($previousState->hasColumn($column)) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$column] is added, but it's already exists!");
                    }

                    if (!$newState->hasColumn($column)) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$column] is added, but it doesn't exists!");
                    }

                    $columnFluent = $newState->columns[$column];

                    $prepares[$table]->columns->added($column, $columnFluent);
                    $prepares[$table]->suggestion->addAdd($column, $this->nameOfAddColumn($column, $table));

                    $added[] = "$table.$column";
                    $this->currentState->get($table)->putColumn($column, $columnFluent);
                }

                foreach ($this->getTravelRenameColumns((array)$travel->whenRenamed, reset($tables)) as [$table, $from, $to]) {
                    if (in_array("$table.$from.$to", $renamed)) {
                        continue;
                    }

                    if (
                        !($newState = $this->outlookState->get($table)) ||
                        !($previousState = $this->currentState->get($table))
                    ) {
                        throw new \Exception("Travel [$relativePath] depended on [$table] table that not exists!");
                    }

                    if (!isset($previousState->columns[$from])) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$from] is renamed, but it doesn't exists!");
                    }

                    if (!$newState->hasColumn($to)) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$from] is renamed to [$table.$to], but it's already exists!");
                    }

                    if ($this->findColumnOldName($table, $newState->columns[$to]) !== $from) {
                        throw new \Exception("Travel [$relativePath] needs to run when [$table.$from] is renamed to [$table.$to], but it doesn't renamed!");
                    }

                    $prepares[$table]->columns->renamed($from, $to);
                    $prepares[$table]->suggestion->addRename($from, $this->nameOfRenameColumn($from, $to, $table));

                    $renamed[] = "$table.$from.$to";

                    $this->currentState->get($table)->renameColumn($from, $to);
                }

                foreach ($prepares as $prepare) {
                    if (!$prepare->isEmpty()) {
                        $this->newMigrations->add($prepare);
                    }
                }
            }

            $this->newMigrations->add(
                new MigrationState(
                    fileName: $this->nameOfTravel($relativePath),
                    table: '',
                    command: MigrationState::COMMAND_TRAVEL,
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
            [$toTable, $toColumn] = $this->getTravelColumnName($value, null);

            return [$toTable ?? $fromTable ?? $defaultTable, $fromColumn, $toColumn];
        });
    }

    protected function getTravelColumnName(string $column, ?string $defaultTable): array
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


    public function setOutlookState(DatabaseState $state): void
    {
        $this->outlookState = $state;
    }

}
