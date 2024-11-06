<?php

namespace Rapid\Laplus\Tests\Guide;

use Rapid\Laplus\Guide\ModelGuide;
use Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide;
use Rapid\Laplus\Tests\TestCase;

class ModelGuideTest extends TestCase
{

    public function test_a()
    {
        $guide = new ModelGuide(_TestModel1ForGuide::class);

        $guide->assertInsertComment(
            <<<'COMMENT'
            /**
             * @GuidePresent
             * @property int $id
             * @property string $name
             * @property float $money
             * @property array $friends List of friends
             * @EndGuidePresent
             */
            COMMENT
        );
    }

}