<?php

namespace Rapid\Laplus\Present\Generate\Concerns;

use Illuminate\Support\Fluent;
use Rapid\Laplus\Present\Generate\Structure\MigrationFileState;
use Rapid\Laplus\Present\Generate\Structure\MigrationState;

trait ExportStubs
{

    protected function makeMigrationCreate(MigrationState $migration)
    {
        $inner = $this->makeMigrationTableInner($migration);

        return new MigrationFileState(
            up: [
                "Schema::create({$this->writeObject($migration->table)}, function (Blueprint \$table) {",
                ...array_map(fn($code) => "    " . $code, $inner->up),
                "});",
            ],
            down: [
                "Schema::drop({$this->writeObject($migration->table)});",
            ],
        );
    }

    protected function makeMigrationTable(MigrationState $migration)
    {
        $inner = $this->makeMigrationTableInner($migration);

        return new MigrationFileState(
            up: [
                "Schema::table({$this->writeObject($migration->table)}, function (Blueprint \$table) {",
                ...array_map(fn($code) => "    " . $code, $inner->up),
                "});",
            ],
            down: [
                "Schema::table({$this->writeObject($migration->table)}, function (Blueprint \$table) {",
                ...array_map(fn($code) => "    " . $code, $inner->down),
                "});",
            ],
        );
    }

    protected function makeMigrationTableInner(MigrationState $migration)
    {
        $up = [];
        $down = [];

        foreach ($migration->columns->removed as $column)
        {
            $up[] = "\$table->dropColumn({$this->writeObject($column)});";
            if ($migration->before)
            {
                $down[] = $this->writeColumn($migration->before->columns[$column]);
            }
        }

        foreach ($migration->columns->renamed as $from => $to)
        {
            $up[] = "\$table->renameColumn({$this->writeObject($from)}, {$this->writeObject($to)});";
            if ($migration->before)
            {
                $down[] = "\$table->renameColumn({$this->writeObject($to)}, {$this->writeObject($from)});";
            }
        }

        foreach ($migration->columns->added as $name => $column)
        {
            $up[] = $this->writeColumn($column);
            $down[] = "\$table->dropColumn({$this->writeObject($name)});";
        }

        foreach ($migration->columns->changed as $name => $column)
        {
            $up[] = $this->writeColumn($column, true);
            if ($migration->before)
            {
                $down[] = $this->writeColumn($migration->before->columns[$name], true);
            }
        }

        // Index

        foreach ($migration->indexes->removed as $index)
        {
            $up[] = "\$table->dropIndex({$this->writeObject($index)});";
            if ($migration->before)
            {
                $down[] = $this->writeCommand($migration->before->indexes[$index]);
            }
        }

        foreach ($migration->indexes->renamed as $from => $to)
        {
            $up[] = "\$table->renameIndex({$this->writeObject($from)}, {$this->writeObject($to)});";
            if ($migration->before)
            {
                $down[] = "\$table->renameIndex({$this->writeObject($to)}, {$this->writeObject($from)});";
            }
        }

        foreach ($migration->indexes->added as $name => $index)
        {
            $up[] = $this->writeCommand($index);
            $down[] = "\$table->dropIndex({$this->writeObject($name)});";
        }

        return new MigrationFileState(
            up: $up,
            down: array_reverse($down),
        );
    }

    protected function writeColumn(Fluent $fluent, bool $change = false)
    {
        $code = match ($fluent->type)
        {
            'enum' => "\$table->enum({$this->writeObject($fluent->name)}, {$this->writeObject($fluent->allowed)})",
            default => "\$table->{$fluent->type}({$this->writeObject($fluent->name)})",
        };

        foreach ($fluent->getAttributes() as $key => $value)
        {
            if (in_array($key, ['type', 'name', 'change', 'allowed']))
                continue;

            if (in_array($key, ['default']))
            {
                $code .= "->{$key}({$this->writeObject($value)})";
                continue;
            }

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

    protected function makeMigrationDrop(MigrationState $migration)
    {
        return new MigrationFileState(
            up: [
                "Schema::drop({$this->writeObject($migration->table)});",
            ],
            down: [],
        );
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

}