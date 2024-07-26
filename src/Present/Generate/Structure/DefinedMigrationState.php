<?php

namespace Rapid\Laplus\Present\Generate\Structure;

class DefinedMigrationState
{

    public function __construct(
        /** @var DefinedTableState[] */
        public array $tables = [],
    )
    {
    }

    public function get(string $name, $default = null)
    {
        return $this->tables[$name] ?? $default;
    }

    public function getOrCreate(string $name)
    {
        return $this->get($name) ?? $this->tables[$name] = new DefinedTableState();
    }

    public function put(string $name, DefinedTableState $table)
    {
        $this->tables[$name] = $table;
    }

    public function __clone() : void
    {
        $this->tables = array_map(fn ($item) => clone $item, $this->tables);
    }

}