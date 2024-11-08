<?php

namespace Rapid\Laplus\Guide\Guides;

use Rapid\Laplus\Guide\Guide;
use Rapid\Laplus\Guide\GuideAuthor;

class DocblockGuide extends Guide
{

    protected function write(GuideAuthor $author)
    {
        $this->modifyFile($author, function ($contents) use ($author)
        {
            $scope = $this->makeScope($contents);

            return $this->commentClass($contents, $author->class, 'Guide', $author->docblock($scope));
        });
    }

}