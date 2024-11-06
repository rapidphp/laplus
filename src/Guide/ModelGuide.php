<?php

namespace Rapid\Laplus\Guide;

use Rapid\Laplus\Present\Present;

/**
 * @internal
 */
class ModelGuide extends GuideAuthor
{

    protected function guide(string $contents)
    {
        /** @var Present $present */
        $present = $this->class::getStaticPresentInstance();

        $contents = $this->commentClass($contents, $this->class, 'GuidePresent', $present->docblock());

        return $contents;
    }
}