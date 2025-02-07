<?php

namespace Rapid\Laplus\Guide\Guides;

use PHPUnit\Framework\Assert;
use Rapid\Laplus\Guide\Guide;
use Rapid\Laplus\Guide\GuideAuthor;
use Rapid\Laplus\Guide\GuideScope;

class TestGuide extends Guide
{

    protected GuideScope $scope;
    protected array $result;
    protected string $class;

    public function __construct(
        ?string $namespace = null,
        array   $uses = [],
    )
    {
        $this->scope = new GuideScope($namespace, $uses);
    }

    public function assertContains(string $expected, string $message = ''): static
    {
        Assert::assertContains($expected, $this->result, $message);

        return $this;
    }

    public function assertWriteComment(string $expected, string $contents, string $message = ''): static
    {
        $this->assertSameTwoStrings($expected, $this->testComment($contents), $message);

        return $this;
    }

    public function assertSame(array $excepted, string $message = ''): static
    {
        Assert::assertSame($excepted, $this->result, $message);

        return $this;
    }

    public function testComment(string $contents): string
    {
        return $this->commentClass($contents, $this->class, 'Guide', $this->result);
    }

    protected function write(GuideAuthor $author): static
    {
        $this->class = $author->class;
        $this->result = $author->docblock($this->scope);

        return $this;
    }

    protected function assertSameTwoStrings(string $expected, string $actual, string $message = ''): void
    {
        Assert::assertSame(
            str_replace("\r", '', $expected),
            str_replace("\r", '', $actual),
            $message
        );
    }

}