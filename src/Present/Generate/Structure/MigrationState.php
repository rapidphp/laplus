<?php

namespace Rapid\Laplus\Present\Generate\Structure;

class MigrationState
{


    public function __construct(
        public string $fileName,
        public string $table,
        public string $command,
        public ?DefinedTableState $before = null,
        public ColumnListState $columns = new ColumnListState(),
        public IndexListState $indexes = new IndexListState(),
        public bool $isLazy = false,
    )
    {
    }

    public function isEmpty()
    {
        return $this->command == 'table' &&
            $this->columns->isEmpty() &&
            $this->indexes->isEmpty();
    }


    protected ?array $suggests = [];

    public function suggestName(string $id, string $name, bool $override = true)
    {
        if (is_null($this->suggests) || (!$override && array_key_exists($id, $this->suggests)))
            return;

        $this->suggests[$id] = $name;
    }

    public function forceName(string $name)
    {
        $this->fileName = $name;
        $this->suggests = null;
    }

    public function getBestFileName()
    {
        if (is_array($this->suggests) && count($this->suggests) == 1)
        {
            return head($this->suggests);
        }

        return $this->fileName;
    }

    public function lazy()
    {
        $this->isLazy = true;
        return $this;
    }

}