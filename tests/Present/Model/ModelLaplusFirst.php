<?php

namespace Rapid\Laplus\Tests\Present\Model;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;

class ModelLaplusFirst extends Model
{
    use HasPresent;

    protected $table = 'firsts';

    protected function present(Present $present)
    {
        $present->id();
        $present->belongsTo(ModelLaplusSecond::class, 'second');
    }

}