<?php

namespace Rapid\Laplus\Tests\Present;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Present\PresentExtension;
use Rapid\Laplus\Tests\TestCase;

class PresentExtensionTest extends TestCase
{

    public function test_extend_with_callback()
    {
        $record = new class extends Model
        {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->id();
            }

            protected static function booted()
            {
                static::extendPresent(function (Present $present)
                {
                    $present->string('name');
                });
            }
        };

        $this->assertSame(['id', 'name'], $record->getFillable());
    }

    public function test_extend_with_extension()
    {
        $record = new class extends Model
        {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->id();
            }

            protected static function booted()
            {
                static::extendPresent(new class extends PresentExtension
                {
                    public function extend(Present $present)
                    {
                        $present->string('name');
                    }
                });
            }
        };

        $this->assertSame(['id', 'name'], $record->getFillable());
    }

    public function test_extend_with_custom_yield()
    {
        $record = new class extends Model
        {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->id();
                $present->yield();
                $present->boolean('last');
            }

            protected static function booted()
            {
                static::extendPresent(function (Present $present)
                {
                    $present->string('name');
                });
            }
        };

        $this->assertSame(['id', 'name', 'last'], $record->getFillable());
    }

}