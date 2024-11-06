<?php

namespace Rapid\Laplus\Tests\Guide;

use Monolog\Test\TestCase;
use Rapid\Laplus\Guide\GuideAuthor;

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

}