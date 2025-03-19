<?php

namespace Rapid\Laplus\Contracts;

use Illuminate\Database\Eloquent\Model;

interface RelationAttr
{
    public function getRelation(Model $model);
}