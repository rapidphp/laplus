<?php

namespace Rapid\Laplus\src;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\HasPresent;

class Test extends Model
{
    use HasPresent;

    public function isRelation($key)
    {
    }

}