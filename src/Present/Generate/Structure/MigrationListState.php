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
        $this->all[] = $migration;
    }

    public function forgetEmpty(): void
    {
        foreach ($this->all as $index => $migration) {
            if ($migration->isEmpty()) {
                unset($this->all[$index]);
            }
        }

        $this->all = array_values($this->all);
    }

}