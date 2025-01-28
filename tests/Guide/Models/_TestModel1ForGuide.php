<?php

namespace Rapid\Laplus\Tests\Guide\Models;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Label\HasLabels;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;

class _TestModel1ForGuide extends Model
{
    use HasPresent, HasLabels;

    protected function present(Present $present)
    {
        $present->id();
        $present->string('name');
        $present->string('money')->typeHint('float');
        $present->json('friends')->docHint('List of friends');
    }

    protected function getLabelTranslatorClass(): ?string
    {
        return _TestLabel1ForGuide::class;
    }

}