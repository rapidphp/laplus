<?php

namespace Rapid\Laplus\Present\Generate\Structure;

class MigrationState
{
    public NameSuggestion $suggestion;
    protected bool $forcedName = false;

    public function __construct(
        public string          $fileName,
        public string          $table,
        public string          $command,
        public ColumnListState $columns = new ColumnListState(),
        public IndexListState  $indexes = new IndexListState(),
        public bool            $isLazy = false,
        public ?string         $travel = null,
    )
    {
        $this->suggestion = new NameSuggestion();
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

    public function forceName(string $name): void
    {
        $this->fileName = $name;
        $this->forcedName = true;
    }

    public function getBestFileName(): string
    {
        if (!$this->forcedName && !is_null($suggest = $this->suggestion->get())) {
            return $suggest;
        }

        return $this->fileName;
    }

    public function lazy()
    {
        $this->isLazy = true;
        return $this;
    }
}