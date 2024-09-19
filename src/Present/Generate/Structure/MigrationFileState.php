<?php

namespace Rapid\Laplus\Present\Generate\Structure;

class MigrationFileState
{

    public function __construct(
        public array $up,
        public array $down,
        public mixed $tag = null,
    )
    {
    }

}