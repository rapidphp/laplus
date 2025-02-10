<?php

namespace Rapid\Laplus\Present;

trait HasTestingPresent
{
    use HasPresent;

    public static function getPresentableInstance(): static
    {
        return new static;
    }

    public function getPresent(): Present
    {
        return $this->makePresent();
    }

}