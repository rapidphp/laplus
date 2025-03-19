<?php

namespace Rapid\Laplus\Discover;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Contracts\RelationAttr;

class DiscoveredRelation
{
    public function __construct(
        /** @var class-string<Model> */
        public string       $model,
        public string       $relation,
        public RelationAttr $attribute,
    )
    {
    }
}