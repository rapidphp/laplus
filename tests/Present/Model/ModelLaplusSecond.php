<?php

namespace Rapid\Laplus\Tests\Present\Model;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;

class ModelLaplusSecond extends Model
{
    use HasPresent;

    protected function present(Present $present)
    {
        $present->id();
        $present->hasOne(ModelLaplusFirst::class, 'first');
        $present->hasMany(ModelLaplusFirst::class, 'firsts');
    }

}