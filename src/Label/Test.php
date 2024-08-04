<?php

namespace Rapid\Laplus\Label;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasLabels;

    public function __get($key)
    {
        return parent::__get($key);
    }

}