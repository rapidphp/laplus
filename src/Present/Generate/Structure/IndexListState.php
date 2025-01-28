<?php

namespace Rapid\Laplus\Present\Generate\Structure;

class IndexListState extends ColumnListState
{

    public function __construct(
        array       $added = [],
        array       $changed = [],
        array       $removed = [],
        array       $renamed = [],
        public bool $depended = false,
    )
    {
        parent::__construct($added, $changed, $removed, $renamed);
    }

}