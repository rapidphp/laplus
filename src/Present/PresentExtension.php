<?php

namespace Rapid\Laplus\Present;

abstract class PresentExtension
{

    public function __invoke(Present $present)
    {
        $this->extend($present);
    }

    public abstract function extend(Present $present);

}