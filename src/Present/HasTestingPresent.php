<?php

namespace Rapid\Laplus\Present;

trait HasTestingPresent
{
    use HasPresent {
        getPresent as __getPresent;
    }

    public static function getPresentableInstance(): static
    {
        return new static;
    }

    public function getPresent(): Present
    {
        unset(static::$_presentObjects[static::class]);

        return $this->__getPresent();
    }

}