<?php

namespace Rapid\Laplus\Tests\Label;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Label\HasLabels;
use Rapid\Laplus\Label\LabelTranslator;
use Rapid\Laplus\Tests\TestCase;

class LabelWithoutPresentTest extends TestCase
{

    public function make(array $attributes = [])
    {
        return new class($attributes) extends Model
        {
            use HasLabels;

            protected function makeLabelTranslator() : ?LabelTranslator
            {
                return new class($this) extends LabelTranslator
                {
                    public function test()
                    {
                        return "Test Passed";
                    }
                };
            }
        };
    }

    public function test_model_with_label_without_present()
    {
        $record = $this->make();

        $this->assertSame("Test Passed", $record->test_label);
    }

}