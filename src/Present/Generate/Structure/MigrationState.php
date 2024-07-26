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
    )
    {
    }

}