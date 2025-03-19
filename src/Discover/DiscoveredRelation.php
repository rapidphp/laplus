<?php

namespace Rapid\Laplus\Discover;

use Illuminate\Database\Eloquent\Model;

class DiscoveredRelation
{
    public function __construct(
        /** @var class-string<Model> */
        public string $model,
        public string $relation,
    )
    {
    }
}