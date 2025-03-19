<?php

namespace Rapid\Laplus\Tests\Present;

use Rapid\Laplus\Laplus;
use Rapid\Laplus\LaplusKit;
use Rapid\Laplus\Resources\FixedResource;
use Rapid\Laplus\Tests\Present\Model\ModelLaplusFirst;
use Rapid\Laplus\Tests\Present\Model\ModelLaplusPresentable;
use Rapid\Laplus\Tests\Present\Model\ModelLaplusSecond;
use Rapid\Laplus\Tests\TestCase;

class DiscoversTest extends TestCase
{
    public function mockLaplus()
    {
        $resources = [
            new FixedResource(__DIR__ . '/Model', '', '', ''),
        ];

        Laplus::partialMock()->shouldReceive('getResources')->andReturn($resources);
    }

    public function testDiscoverModels()
    {
        $this->mockLaplus();

        $this->assertSame(
            [ModelLaplusFirst::class, ModelLaplusPresentable::class, ModelLaplusSecond::class],
            LaplusKit::discoverModels(),
        );
    }

    public function testDiscoverBelongsTo()
    {
        $this->mockLaplus();

        $discovered = LaplusKit::discoverBelongsToRelations();

        $this->assertCount(1, $discovered);
        $this->assertSame(ModelLaplusFirst::class, $discovered[0]->model);
        $this->assertSame('second', $discovered[0]->relation);
    }

    public function testDiscoverBelongsToSpecific()
    {
        $this->mockLaplus();

        $discovered = LaplusKit::discoverBelongsToRelations(ModelLaplusSecond::class);

        $this->assertCount(1, $discovered);
        $this->assertSame(ModelLaplusFirst::class, $discovered[0]->model);
        $this->assertSame('second', $discovered[0]->relation);
    }

    public function testDiscoverBelongsToSpecificFailed()
    {
        $this->mockLaplus();

        $discovered = LaplusKit::discoverBelongsToRelations(ModelLaplusPresentable::class);

        $this->assertCount(0, $discovered);
    }
}