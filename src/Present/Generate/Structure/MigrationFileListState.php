<?php

namespace Rapid\Laplus\Present\Generate\Structure;

class MigrationFileListState
{

    public function __construct(
        /** @var MigrationFileState[] */
        public array $files = [],
    )
    {
    }

}