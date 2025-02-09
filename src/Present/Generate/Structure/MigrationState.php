<?php

namespace Rapid\Laplus\Present\Generate\Structure;

class MigrationState
{
    protected ?array $suggests = [];

    public function __construct(
        public string          $fileName,
        public string          $table,
        public string          $command,
        public ?TableState     $before = null,
        public ColumnListState $columns = new ColumnListState(),
        public IndexListState  $indexes = new IndexListState(),
        public bool            $isLazy = false,
        public ?string         $travel = null,
    )
    {
    }

    public const COMMAND_TABLE = 'table';
    public const COMMAND_DROP = 'drop';
    public const COMMAND_TRAVEL = 'travel';

    public function isEmpty(): bool
    {
        return $this->command == MigrationState::COMMAND_TABLE &&
            $this->columns->isEmpty() &&
            $this->indexes->isEmpty();
    }

    public function suggestName(string $id, string $name, bool $override = true): void
    {
        if (is_null($this->suggests) || (!$override && array_key_exists($id, $this->suggests)))
            return;

        $this->suggests[$id] = $name;
    }

    public function forceName(string $name): void
    {
        $this->fileName = $name;
        $this->suggests = null;
    }

    public function getBestFileName(): string
    {
        if (is_array($this->suggests) && count($this->suggests) == 1) {
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