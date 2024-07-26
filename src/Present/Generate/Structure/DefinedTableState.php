<?php

namespace Rapid\Laplus\Present\Generate\Structure;

use Illuminate\Support\Fluent;

class DefinedTableState
{

    public function __construct(
        /** @var Fluent[] */
        public array $columns = [],
        /** @var Fluent[] */
        public array $indexes = [],
    )
    {
    }

}