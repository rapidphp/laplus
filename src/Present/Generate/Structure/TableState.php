<?php

namespace Rapid\Laplus\Present\Generate\Structure;

use Illuminate\Support\Fluent;

class TableState
{

    public function __construct(
        /** @var array<string, Fluent> */
        public array $columns = [],
        /** @var array<string, Fluent> */
        public array $indexes = [],
    )
    {
    }

    public function putColumn(string $name, Fluent $column): void
    {
        $this->columns[$name] = $column;
    }

    public function hasColumn(string $name): bool
    {
        return isset($this->columns[$name]);
    }

    public function dropColumn(string $name): void
    {
        unset($this->columns[$name]);
    }

    public function renameColumn(string $from, string $to): void
    {
        $previous = clone $this->columns[$from];
        $previous['name'] = $to;
        $this->columns[$to] = $previous;

        $this->dropColumn($from);
    }

    public function putIndex(string $name, Fluent $index): void
    {
        $this->indexes[$name] = $index;
    }

    public function hasIndex(string $name): bool
    {
        return isset($this->indexes[$name]);
    }

    public function dropIndex(string $name): void
    {
        unset($this->indexes[$name]);
    }

}