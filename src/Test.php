<?php

namespace Rapid\Laplus\src;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;

class Test extends Model
{
    use HasPresent;

    public function present(Present $present)
    {
        $present->id();
        $present->file('image');
        $present->timestamps();
    }

}