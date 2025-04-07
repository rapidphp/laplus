<?php

namespace Rapid\Laplus\Tests\Present;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Support\Traits\HasPresentAttributes;
use Rapid\Laplus\Tests\TestCase;

class PresentGetterSetterTest extends TestCase
{

    public function test_the_getter_and_setter_will_calls()
    {
        $record = (new class extends Model {
            use HasPresent, HasPresentAttributes;

            public bool $getCalled = false;
            public bool $setCalled = false;

            protected function present(Present $present)
            {
                $present->string('name')
                    ->getUsing(static function (?string $value, Model $record) {
                        $record->getCalled = true;
                    })
                    ->setUsing(static function (?string $value, Model $record) {
                        $record->setCalled = true;
                    });
            }
        });

        $this->assertFalse($record->getCalled);
        $this->assertFalse($record->setCalled);

        $record->name = 'foo';

        $this->assertFalse($record->getCalled);
        $this->assertTrue($record->setCalled);

        $name = $record->name;

        $this->assertTrue($record->getCalled);
        $this->assertTrue($record->setCalled);
    }

    public function test_check_getter_and_setter_works()
    {
        $record = (new class extends Model {
            use HasPresent, HasPresentAttributes;

            protected function present(Present $present)
            {
                $present->string('name')
                    ->getUsing(static function (string $value) {
                        return ucfirst($value);
                    })
                    ->setUsing(static function (string $value, Model $record, string $key, array &$attributes) {
                        $attributes[$key] = strtolower($value);
                        return true;
                    });
            }
        })->setRawAttributes(['name' => 'aLi']);

        $this->assertSame('aLi', $record->getAttributes()['name']);
        $this->assertSame('ALi', $record->name);

        $record->name = 'REza';
        $this->assertSame('reza', $record->getAttributes()['name']);
        $this->assertSame('Reza', $record->name);

        $this->assertTrue($record->setAttribute('name', 'maHDi'));
        $this->assertSame('mahdi', $record->getAttributes()['name']);
        $this->assertSame('Mahdi', $record->name);
    }

}