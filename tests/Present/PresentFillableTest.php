<?php

namespace Rapid\Laplus\Tests\Present;

use Rapid\Laplus\Present\Generate\Testing\AnonymousTestingPresentableModel;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\TestCase;

class PresentFillableTest extends TestCase
{
    public function test_normal_fillable()
    {
        $target = (new AnonymousTestingPresentableModel(callback: function (Present $present) {
            $present->id();
            $present->string('foo');
            $present->integer('bar');
            $present->json('data');
            $present->timestamps();
        }));

        $this->assertSame([
            'foo', 'bar', 'data',
        ], $target->getFillable());
    }

    public function test_id_is_not_fillable()
    {
        $target = (new AnonymousTestingPresentableModel(callback: function (Present $present) {
            $present->id();
        }));

        $this->assertNotContains('id', $target->getFillable());
    }

    public function test_id_can_be_fillable()
    {
        $target = (new AnonymousTestingPresentableModel(callback: function (Present $present) {
            $present->id()->fillable();
        }));

        $this->assertContains('id', $target->getFillable());
    }
}