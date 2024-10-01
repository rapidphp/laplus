<?php

namespace Rapid\Laplus\Tests\Label;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Label\HasLabels;
use Rapid\Laplus\Label\LabelTranslator;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\TestCase;

class LabelTranslatorBooleansTest extends TestCase
{

    public function make(array $attributes = [])
    {
        return new class($attributes) extends Model
        {
            use HasPresent, HasLabels;

            protected function makeLabelTranslator() : ?LabelTranslator
            {
                return new class($this) extends LabelTranslator
                {
                    public function tf()
                    {
                        return $this->asTrueFalse;
                    }

                    public function oo()
                    {
                        return $this->asOnOff;
                    }

                    public function yn()
                    {
                        return $this->asYesNo;
                    }

                    public function ynNotNull()
                    {
                        $this->setCurrentAttribute('yn');

                        return $this->asYesNoNotNull;
                    }
                };
            }

            protected function present(Present $present)
            {
                $present->id();
                $present->boolean('tf')->nullable();
                $present->boolean('oo')->nullable();
                $present->boolean('yn')->nullable();
                $present->timestamps();
            }
        };
    }

    public function test_true_false_booleans()
    {
        $this->assertSame(
            'True',
            $this->make(['tf' => true])->label('tf')
        );

        $this->assertSame(
            'True',
            $this->make(['tf' => "A True Value"])->label('tf')
        );

        $this->assertSame(
            'False',
            $this->make(['tf' => false])->label('tf')
        );

        $this->assertSame(
            'False',
            $this->make(['tf' => ''])->label('tf')
        );

        $this->assertSame(
            'Undefined',
            $this->make(['tf' => null])->label('tf')
        );
    }

    public function test_on_off_booleans()
    {
        $this->assertSame(
            'On',
            $this->make(['oo' => true])->label('oo')
        );

        $this->assertSame(
            'On',
            $this->make(['oo' => "A True Value"])->label('oo')
        );

        $this->assertSame(
            'Off',
            $this->make(['oo' => false])->label('oo')
        );

        $this->assertSame(
            'Off',
            $this->make(['oo' => ''])->label('oo')
        );

        $this->assertSame(
            'Undefined',
            $this->make(['oo' => null])->label('oo')
        );
    }

    public function test_yes_no_booleans()
    {
        $this->assertSame(
            'Yes',
            $this->make(['yn' => true])->label('yn')
        );

        $this->assertSame(
            'Yes',
            $this->make(['yn' => "A True Value"])->label('yn')
        );

        $this->assertSame(
            'No',
            $this->make(['yn' => false])->label('yn')
        );

        $this->assertSame(
            'No',
            $this->make(['yn' => ''])->label('yn')
        );

        $this->assertSame(
            'Undefined',
            $this->make(['yn' => null])->label('yn')
        );
    }

    public function test_yes_no_not_null_booleans()
    {
        $this->assertSame(
            'Yes',
            $this->make(['yn' => true])->label('yn_not_null')
        );

        $this->assertSame(
            'No',
            $this->make(['yn' => false])->label('yn_not_null')
        );

        $this->assertSame(
            'No',
            $this->make(['yn' => null])->label('yn_not_null')
        );
    }

}