<?php

namespace Rapid\Laplus\Guide\Attributes;

use Rapid\Laplus\Guide\GuideScope;

interface DocblockAttributeContract
{

    public function docblock(GuideScope $scope, $reflection) : array;

}