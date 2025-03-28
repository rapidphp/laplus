<?php

namespace Rapid\Laplus\Support\Extensions;

use Rapid\Laplus\Present\Attributes\Column;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Present\PresentExtension;

class FillableIds extends PresentExtension
{
    public function after(Present $present): void
    {
        if (($id = $present->getAttribute('id')) instanceof Column) {
            $id->fillable();
        }
    }
}