<?php

namespace Rapid\Laplus\Support\Extensions;

use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Present\PresentExtension;

class IdAndTimestamps extends PresentExtension
{
    public function before(Present $present): void
    {
        $present->id();
    }

    public function after(Present $present): void
    {
        $present->timestamps();
    }
}