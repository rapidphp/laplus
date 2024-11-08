<?php

namespace Rapid\Laplus\Present;

abstract class PresentExtension
{

    public abstract function extend(Present $present);

    public function __invoke(Present $present)
    {
        $this->extend($present);
    }

}