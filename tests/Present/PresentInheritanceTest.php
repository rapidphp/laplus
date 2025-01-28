<?php

namespace Rapid\Laplus\Tests\Present;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\TestCase;

class PresentInheritanceTest extends TestCase
{

    public function test_call_the_parent()
    {
        $record = new class extends Model {
            use HasPresent;

            protected function makePresent(): Present
            {
                return new class($this) extends Present {
                    protected function thinkItsParent()
                    {
                        $this->id();
                        $this->yield();
                        $this->timestamps();
                    }

                    protected function present()
                    {
                        $this->atYield($this->thinkItsParent(...), function () {
                            $this->string('name');
                        });
                    }
                };
            }
        };

        $this->assertSame(['id', 'name', 'created_at', 'updated_at'], $record->getFillable());
    }

    public function test_call_the_chained_parents()
    {
        $record = new class extends Model {
            use HasPresent;

            protected function makePresent(): Present
            {
                return new class($this) extends Present {
                    protected function thinkItsParentOfParent()
                    {
                        $this->id();
                        $this->yield();
                        $this->timestamps();
                    }

                    protected function thinkItsParent()
                    {
                        $this->atYield($this->thinkItsParentOfParent(...), function () {
                            $this->string('name');
                            $this->yield();
                            $this->integer('age');
                        });
                    }

                    protected function present()
                    {
                        $this->atYield($this->thinkItsParent(...), function () {
                            $this->string('friend_name');
                            $this->yield();
                        });
                    }
                };
            }
        };

        $this->assertSame(['id', 'name', 'friend_name', 'age', 'created_at', 'updated_at'], $record->getFillable());
    }

}