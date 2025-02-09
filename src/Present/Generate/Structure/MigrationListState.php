<?php

namespace Rapid\Laplus\Present\Generate\Structure;

class MigrationListState
{

    public function __construct(
        /** @var MigrationState[] */
        public array $all = [],
    )
    {
    }

    public function add(MigrationState $migration): void
    {
        $this->all[$migration->fileName] = $migration;
    }

    public function remove(string|MigrationState $fileName): void
    {
        unset($this->all[is_string($fileName) ? $fileName : $fileName->fileName]);
    }

    public function forgetEmpty(): void
    {
        foreach ($this->all as $index => $migration) {
            if ($migration->isEmpty()) {
                unset($this->all[$index]);
            }
        }
    }

}