<?php

namespace Rapid\Laplus\Guide;

/**
 * @internal
 */
abstract class GuideAuthor
{

    public function __construct(
        public Guide  $guide,
        public string $class,
    )
    {
    }

    /**
     * Generate the docblock values
     *
     * @param GuideScope $scope
     * @return array
     */
    public abstract function docblock(GuideScope $scope): array;

}