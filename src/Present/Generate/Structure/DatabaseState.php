<?php

namespace Rapid\Laplus\Present\Generate\Structure;

class DatabaseState
{

    public function __construct(
        /** @var TableState[] */
        public array $tables = [],
    )
    {
    }

    public function getOrCreate(string $name): TableState
    {
        return $this->tables[$name] ??= new TableState();
    }

    public function get(string $name, $default = null)
    {
        return $this->tables[$name] ?? $default;
    }

    public function put(string $name, TableState $table): void
    {
        $this->tables[$name] = $table;
    }

    public function __clone(): void
    {
        $this->tables = array_map(fn($item) => clone $item, $this->tables);
    }

}