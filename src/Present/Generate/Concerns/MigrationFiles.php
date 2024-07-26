<?php

namespace Rapid\Laplus\Present\Generate\Concerns;

use Rapid\Laplus\Present\Generate\Structure\MigrationFileListState;
use Rapid\Laplus\Present\Generate\Structure\MigrationListState;

trait MigrationFiles
{

    /**
     * Export migration files
     *
     * @return MigrationFileListState
     */
    public function exportMigrationFiles()
    {
        return $this->exportMigrationFilesFrom(
            $this->generate()
        );
    }

    /**
     * Export migration files from MigrationListState object
     *
     * @param MigrationListState $migrations
     * @return MigrationFileListState
     */
    protected function exportMigrationFilesFrom(MigrationListState $migrations)
    {
        $files = new MigrationFileListState();
        $createdTables = isset($this->definedMigrationState) ? array_keys($this->definedMigrationState->tables) : [];

        $dateIndex = time();

        foreach ($migrations->all as $migration)
        {
            $name = date('Y_m_d_His', $dateIndex++) . '_' . $migration->getBestFileName();

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
                    // die("Error"); // TODO : Should change
            }
        }

        return $files;
    }

    /**
     * Export migration stubs
     *
     * @param MigrationFileListState $files
     * @return array<string, string>
     */
    public function exportMigrationStubs(MigrationFileListState $files)
    {
        $stub = file_get_contents(__DIR__ . '/../../../Commands/stubs/migration.stub');

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

}