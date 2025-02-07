<?php

namespace Rapid\Laplus\Tests\Present\Model;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Supports\Traits\HasPresentAttributes;

class ModelLaplusPresentable extends Model
{
    use HasPresent, HasPresentAttributes;

    protected $table = 'tests';

    protected function present(Present $present)
    {
        $present->id();
        $present->text('wants_to_rename')->old('old_name');
        $present->text('wants_to_change_type');
        // $present->text('wants_to_remove');
    }

}