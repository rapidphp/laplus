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

    public function add(MigrationState $migration)
    {
        $this->all[$migration->fileName] = $migration;
    }

    public function remove(string|MigrationState $fileName)
    {
        unset($this->all[is_string($fileName) ? $fileName : $fileName->fileName]);
    }

}