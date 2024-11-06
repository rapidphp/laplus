<?php

namespace Rapid\Laplus\Guide;

/**
 * @internal
 */
class ModelGuide extends GuideAuthor
{

    protected function guide(string $contents)
    {
        $contents = $this->commentClass($contents, $this->class, 'GuidePresent', ['Test', 'Test2']);

        return $contents;
    }
}