<?php

namespace Rapid\Laplus\Guide\Attributes;

interface DocblockAttributeContract
{

    public function docblock($reflection) : array;

}