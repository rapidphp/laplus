<?php

namespace Rapid\Laplus\Guide\Guides;

use PHPUnit\Framework\Assert;
use Rapid\Laplus\Guide\Guide;
use Rapid\Laplus\Guide\GuideAuthor;
use Rapid\Laplus\Guide\GuideScope;

class TestGuide extends Guide
{

    public function __construct(
        ?string $namespace = null,
        array $uses = [],
    )
    {
        $this->scope = new GuideScope($namespace, $uses);
    }

    protected GuideScope $scope;

    protected array $result;

    protected string $class;

    protected function write(GuideAuthor $author)
    {
        $this->class = $author->class;
        $this->result = $author->docblock($this->scope);
    }


    public function testComment(string $contents) : string
    {
        return $this->commentClass($contents, $this->class, 'Guide', $this->result);
    }

    public function assertSame(array $excepted, string $message = '')
    {
        Assert::assertSame($excepted, $this->result, $message);
    }

    public function assertContains(string $expected, string $message = '')
    {
        Assert::assertContains($expected, $this->result, $message);
    }

    public function assertWriteComment(string $expected, string $contents, string $message = '')
    {
        Assert::assertSame($expected, $this->testComment($contents), $message);
    }

}