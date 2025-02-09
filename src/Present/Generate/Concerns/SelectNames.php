<?php

namespace Rapid\Laplus\Present\Generate\Concerns;

trait SelectNames
{

    public function nameOfRenameColumn(string $old, string $new, string $table): string
    {
        return "rename_{$old}_to_{$new}_in_{$table}_table";
    }

    public function nameOfModifyColumn(string $column, array $changes, string $table): string
    {
        if (count($changes) == 1) {
            return "change_{$column}_{$changes[0]}_in_{$table}_table";
        }

        if (count($changes) == 2) {
            return "change_{$column}_{$changes[0]}_and_{$changes[1]}_in_{$table}_table";
        }

        return "change_{$column}_in_{$table}_table";
    }

    public function nameOfAddColumn(string $column, string $table): string
    {
        return "add_{$column}_to_{$table}_table";
    }

    public function nameOfCreateTable(string $table): string
    {
        return "create_{$table}_table";
    }

    public function nameOfModifyTable(string $table): string
    {
        return "modify_{$table}_table";
    }

    public function nameOfDropTable(string $table): string
    {
        return "drop_{$table}_table";
    }

    public function nameOfAddIndexes(string $table): string
    {
        return "add_indexes_to_{$table}_table";
    }

    public function nameOfRemoveIndexes(string $table): string
    {
        return "remove_indexes_from_{$table}_table";
    }

    public function nameOfRemoveColumn(string $column, string $table): string
    {
        return "remove_{$column}_from_{$table}_table";
    }

    public function nameOfSoftRemoveColumn(string $column, string $table): string
    {
        return "soft_remove_{$column}_from_{$table}_table";
    }

    public function nameOfTravel(string $relativePath): string
    {
        return preg_replace('/^[0-9_\-]+/', '', pathinfo($relativePath, PATHINFO_FILENAME));
    }

}
