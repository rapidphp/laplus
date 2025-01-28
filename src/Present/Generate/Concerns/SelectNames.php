<?php

namespace Rapid\Laplus\Present\Generate\Concerns;

trait SelectNames
{

    public function nameOfRenameColumn(string $old, string $new, string $table)
    {
        return "rename_{$old}_to_{$new}_in_{$table}_table";
    }

    public function nameOfModifyColumn(string $column, array $changes, string $table)
    {
        if (count($changes) == 1) {
            return "change_{$column}_{$changes[0]}_in_{$table}_table";
        }

        if (count($changes) == 2) {
            return "change_{$column}_{$changes[0]}_and_{$changes[1]}_in_{$table}_table";
        }

        return "change_{$column}_in_{$table}_table";
    }

    public function nameOfAddColumn(string $column, string $table)
    {
        return "add_{$column}_to_{$table}_table";
    }

    public function nameOfCreateTable(string $table)
    {
        return "create_{$table}_table";
    }

    public function nameOfModifyTable(string $table)
    {
        return "modify_{$table}_table";
    }

    public function nameOfDropTable(string $table)
    {
        return "drop_{$table}_table";
    }

    public function nameOfAddIndexes(string $table)
    {
        return "add_indexes_to_{$table}_table";
    }

    public function nameOfRemoveIndexes(string $table)
    {
        return "remove_indexes_from_{$table}_table";
    }

    public function nameOfRemoveColumn(string $column, string $table)
    {
        return "remove_{$column}_from_{$table}_table";
    }

}
