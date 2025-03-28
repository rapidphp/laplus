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
        $record = new class extends Model {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->id();
            }

            protected static function booted()
            {
                static::extendPresent(function (Present $present) {
                    $present->string('name');
                });
            }
        };

        $this->assertSame(['name'], $record->getFillable());
    }

    public function test_extend_with_extension()
    {
        $record = new class extends Model {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->id();
            }

            protected static function booted()
            {
                static::extendPresent(new class extends PresentExtension {
                    public function extend(Present $present): void
                    {
                        $present->string('name');
                    }
                });
            }
        };

        $this->assertSame(['name'], $record->getFillable());
    }

    public function test_extend_with_custom_yield()
    {
        $record = new class extends Model {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->id();
                $present->yield();
                $present->boolean('last');
            }

            protected static function booted()
            {
                static::extendPresent(function (Present $present) {
                    $present->string('name');
                });
            }
        };

        $this->assertSame(['name', 'last'], $record->getFillable());
    }

    public function test_extend_before_hook()
    {
        new class extends Model {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->id();
            }

            protected static function booted()
            {
                static::extendPresent(new class extends PresentExtension {
                    public function before(Present $present): void
                    {
                        TestCase::assertNull($present->getAttribute('id'));
                    }
                });
            }
        };
    }

    public function test_extend_after_hook()
    {
        new class extends Model {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->string('slug');
            }

            protected static function booted()
            {
                static::extendPresent(new class extends PresentExtension {
                    public function after(Present $present): void
                    {
                        TestCase::assertNotNull($present->getAttribute('slug'));
                        TestCase::assertEmpty($present->fillable);
                    }
                });
            }
        };
    }

    public function test_extend_finally_hook()
    {
        new class extends Model {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->string('slug');
            }

            protected static function booted()
            {
                static::extendPresent(new class extends PresentExtension {
                    public function finally(Present $present): void
                    {
                        TestCase::assertNotNull($present->getAttribute('slug'));
                        TestCase::assertSame(['slug'], $present->fillable);
                    }
                });
            }
        };
    }
}