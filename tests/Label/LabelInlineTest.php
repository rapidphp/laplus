<?php

namespace Rapid\Laplus\Tests\Present;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Label\HasLabels;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\TestCase;

class LabelInlineTest extends TestCase
{

    public function test_label_that_defined_normally()
    {
        $record = $this->make(['age' => 20]);

        $this->assertSame("20 years old", $record->label('age'));
        $this->assertSame("20 years old", $record->age_label);
    }

    public function make(array $attributes = [])
    {
        return new class($attributes) extends Model {
            use HasPresent, HasLabels;

            protected function present(Present $present)
            {
                $present->id();
                $present->integer('age')->label(fn($age) => "$age years old");
                $present->string('fixed')->label("Doesn't matter what is value");
                $present->string('status')->label(fn($status, $default = 'Not Set') => $status ?? $default);
            }
        };
    }

    public function test_label_that_defined_fixed()
    {
        $record = $this->make(['fixed' => "Something Awesome"]);

        $this->assertSame("Doesn't matter what is value", $record->label('fixed'));
        $this->assertSame("Doesn't matter what is value", $record->fixed_label);
        $this->assertSame("Doesn't matter what is value", $record->fixed_label());
    }

    public function test_label_that_defined_with_parameters()
    {
        $record = $this->make();

        $this->assertSame("Default", $record->label('status', "Default"));
        $this->assertSame("Not Set", $record->status_label);
        $this->assertSame("Default", $record->status_label("Default"));
    }

}