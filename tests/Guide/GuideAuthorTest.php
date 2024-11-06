<?php

namespace Rapid\Laplus\Tests\Guide;

use Monolog\Test\TestCase;
use Rapid\Laplus\Guide\GuideAuthor;
use Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide;

class GuideAuthorTest extends TestCase
{

    public function test_writing_with_no_comments()
    {
        $guide = new class('FooModel') extends GuideAuthor
        {
            protected function guide(string $contents)
            {
                return $this->commentClass($contents, $this->class, 'GuideTest', ['Foo', 'Bar']);
            }
        };

        $this->assertSame(
            <<<CODE
            <?php
            /**
             * @GuideTest
             * Foo
             * Bar
             * @EndGuideTest
             */
            class FooModel extends Model
            { }
            CODE,
            $guide->testWrite(
                <<<CODE
                <?php
                class FooModel extends Model
                { }
                CODE
            )
        );
    }

    public function test_writing_with_text_comments()
    {
        $guide = new class('FooModel') extends GuideAuthor
        {
            protected function guide(string $contents)
            {
                return $this->commentClass($contents, $this->class, 'GuideTest', ['Foo', 'Bar']);
            }
        };

        $this->assertSame(
            <<<CODE
            <?php
            /**
             * This is testing
             * 
             * @GuideTest
             * Foo
             * Bar
             * @EndGuideTest
             */
            class FooModel extends Model
            { }
            CODE,
            $guide->testWrite(
                <<<CODE
                <?php
                /**
                 * This is testing
                 */
                class FooModel extends Model
                { }
                CODE
            )
        );
    }

    public function test_writing_with_exists_tag()
    {
        $guide = new class('FooModel') extends GuideAuthor
        {
            protected function guide(string $contents)
            {
                return $this->commentClass($contents, $this->class, 'GuideTest', ['Foo', 'Bar']);
            }
        };

        $this->assertSame(
            <<<CODE
            <?php
            /**
             * This is testing
             * 
             * @GuideTest
             * Foo
             * Bar
             * @EndGuideTest
             */
            class FooModel extends Model
            { }
            CODE,
            $guide->testWrite(
                <<<CODE
                <?php
                /**
                 * This is testing
                 * 
                 * @GuideTest
                 * This tag is already exists
                 * @EndGuideTest
                 */
                class FooModel extends Model
                { }
                CODE
            )
        );
    }

    public function test_writing_classes_with_simplify()
    {
        $guide = new class('FooModel') extends GuideAuthor
        {
            protected function guide(string $contents)
            {
                $scope = $this->makeScope($contents);

                return $this->commentClass($contents, $this->class, 'GuideTest', [
                    $scope->typeHint(_TestModel1ForGuide::class),
                    $scope->typeHint('int|' . _TestModel1ForGuide::class . '|string'),
                ]);
            }
        };

        $this->assertSame(
            <<<CODE
            <?php
            /**
             * @GuideTest
             * \Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide
             * int|\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide|string
             * @EndGuideTest
             */
            class FooModel extends Model
            { }
            CODE,
            $guide->testWrite(
                <<<CODE
                <?php
                class FooModel extends Model
                { }
                CODE
            )
        );

        $this->assertSame(
            <<<CODE
            <?php
            namespace Rapid\Laplus\Tests\Guide\Models;
            /**
             * @GuideTest
             * _TestModel1ForGuide
             * int|_TestModel1ForGuide|string
             * @EndGuideTest
             */
            class FooModel extends Model
            { }
            CODE,
            $guide->testWrite(
                <<<CODE
                <?php
                namespace Rapid\Laplus\Tests\Guide\Models;
                class FooModel extends Model
                { }
                CODE
            )
        );

        $this->assertSame(
            <<<CODE
            <?php
            namespace Rapid\Laplus\Tests\Guide;
            /**
             * @GuideTest
             * Models\_TestModel1ForGuide
             * int|Models\_TestModel1ForGuide|string
             * @EndGuideTest
             */
            class FooModel extends Model
            { }
            CODE,
            $guide->testWrite(
                <<<CODE
                <?php
                namespace Rapid\Laplus\Tests\Guide;
                class FooModel extends Model
                { }
                CODE
            )
        );

        $this->assertSame(
            <<<CODE
            <?php
            use Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide;
            /**
             * @GuideTest
             * _TestModel1ForGuide
             * int|_TestModel1ForGuide|string
             * @EndGuideTest
             */
            class FooModel extends Model
            { }
            CODE,
            $guide->testWrite(
                <<<CODE
                <?php
                use Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide;
                class FooModel extends Model
                { }
                CODE
            )
        );

        $this->assertSame(
            <<<CODE
            <?php
            use Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide as Test1;
            /**
             * @GuideTest
             * Test1
             * int|Test1|string
             * @EndGuideTest
             */
            class FooModel extends Model
            { }
            CODE,
            $guide->testWrite(
                <<<CODE
                <?php
                use Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide as Test1;
                class FooModel extends Model
                { }
                CODE
            )
        );
    }

}