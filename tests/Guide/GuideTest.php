<?php

namespace Rapid\Laplus\Tests\Guide;

use Monolog\Test\TestCase;
use Rapid\Laplus\Guide\GuideAuthor;
use Rapid\Laplus\Guide\Guides\TestGuide;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide;

class GuideTest extends TestCase
{

    public function test_docblock_extract()
    {
        $guide = new TestGuide;
        $author = new class($guide, 'FooModel') extends GuideAuthor
        {
            public function docblock(GuideScope $scope) : array
            {
                return ['Foo', 'Bar'];
            }
        };
        $guide->run($author);

        $guide->assertSame([
            'Foo',
            'Bar',
        ]);
    }

    public function test_comment_with_no_comments()
    {
        $guide = new TestGuide;
        $author = new class($guide, 'FooModel') extends GuideAuthor
        {
            public function docblock(GuideScope $scope) : array
            {
                return ['Foo', 'Bar'];
            }
        };
        $guide->run($author);

        $guide->assertWriteComment(
            <<<CODE
            <?php
            /**
             * @Guide
             * Foo
             * Bar
             * @EndGuide
             */
            class FooModel extends Model
            { }
            CODE,
            <<<CODE
            <?php
            class FooModel extends Model
            { }
            CODE
        );

    }

    public function test_comment_with_normal_comments()
    {
        $guide = new TestGuide;
        $author = new class($guide, 'FooModel') extends GuideAuthor
        {
            public function docblock(GuideScope $scope) : array
            {
                return ['Foo', 'Bar'];
            }
        };
        $guide->run($author);

        $guide->assertWriteComment(
            <<<CODE
            <?php
            /**
             * This is testing
             * 
             * @Guide
             * Foo
             * Bar
             * @EndGuide
             */
            class FooModel extends Model
            { }
            CODE,
            <<<CODE
            <?php
            /**
             * This is testing
             */
            class FooModel extends Model
            { }
            CODE
        );

    }

    public function test_comment_with_exists_guide_comments()
    {
        $guide = new TestGuide;
        $author = new class($guide, 'FooModel') extends GuideAuthor
        {
            public function docblock(GuideScope $scope) : array
            {
                return ['Foo', 'Bar'];
            }
        };
        $guide->run($author);

        $guide->assertWriteComment(
            <<<CODE
            <?php
            /**
             * @Guide
             * Foo
             * Bar
             * @EndGuide
             */
            class FooModel extends Model
            { }
            CODE,
            <<<CODE
            <?php
            /**
             * @Guide
             * Old Guide
             * @EndGuide
             */
            class FooModel extends Model
            { }
            CODE
        );

    }

    public function test_comment_classes_with_no_simplify()
    {
        $guide = new TestGuide;
        $author = new class($guide, 'FooModel') extends GuideAuthor
        {
            public function docblock(GuideScope $scope) : array
            {
                return [
                    $scope->typeHint(_TestModel1ForGuide::class),
                ];
            }
        };
        $guide->run($author);

        $guide->assertSame([
            "\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide",
        ]);
    }

    public function test_comment_classes_with_same_namespace_simplify()
    {
        $guide = new TestGuide("Rapid\Laplus\Tests\Guide\Models");
        $author = new class($guide, 'FooModel') extends GuideAuthor
        {
            public function docblock(GuideScope $scope) : array
            {
                return [
                    $scope->typeHint(_TestModel1ForGuide::class),
                ];
            }
        };
        $guide->run($author);

        $guide->assertSame([
            "_TestModel1ForGuide",
        ]);
    }

    public function test_comment_classes_with_sub_namespace_simplify()
    {
        $guide = new TestGuide("Rapid\Laplus\Tests\Guide");
        $author = new class($guide, 'FooModel') extends GuideAuthor
        {
            public function docblock(GuideScope $scope) : array
            {
                return [
                    $scope->typeHint(_TestModel1ForGuide::class),
                ];
            }
        };
        $guide->run($author);

        $guide->assertSame([
            "Models\_TestModel1ForGuide",
        ]);
    }

    public function test_comment_classes_with_used_simplify()
    {
        $guide = new TestGuide(null, [_TestModel1ForGuide::class => 'TestModel']);
        $author = new class($guide, 'FooModel') extends GuideAuthor
        {
            public function docblock(GuideScope $scope) : array
            {
                return [
                    $scope->typeHint(_TestModel1ForGuide::class),
                ];
            }
        };
        $guide->run($author);

        $guide->assertSame([
            "TestModel",
        ]);
    }

}