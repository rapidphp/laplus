<?php

namespace Rapid\Laplus\Present;

use Rapid\Laplus\Guide\GuideScope;

abstract class PresentExtension
{
    public function extend(Present $present): void
    {
    }

    public function before(Present $present): void
    {
    }

    public function after(Present $present): void
    {
    }

    public function finally(Present $present): void
    {
    }

    public function docblock(Present $present, GuideScope $scope): array
    {
        return [];
    }
}