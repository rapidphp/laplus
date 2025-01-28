<?php

namespace Rapid\Laplus\Tests\Label;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Label\HasLabels;
use Rapid\Laplus\Label\LabelTranslator;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\TestCase;

class LabelTranslatorTest extends TestCase
{

    public function test_label_that_defined_normally()
    {
        $record = $this->make(['age' => 20]);

        $this->assertSame("20 years old", $record->label('age'));
        $this->assertSame("20 years old", $record->age_label);
    }

    public function make(array $attributes = [])
    {
        return (new class extends Model {
            use HasPresent, HasLabels;

            protected function makeLabelTranslator(): ?LabelTranslator
            {
                return new class($this) extends LabelTranslator {
                    public function age()
                    {
                        return "{$this->record->age} years old";
                    }

                    public function fixed()
                    {
                        return "Doesn't matter what is value";
                    }

                    public function status($default = 'Not Set')
                    {
                        return $this->record->status ?? $default;
                    }

                    public function empty()
                    {
                        return $this->value;
                    }

                    public function fixedAlias()
                    {
                        $this->setCurrentAttribute('fixed');

                        return $this->value;
                    }

                    protected function getUndefined(): string
                    {
                        return 'Undefined Label';
                    }
                };
            }

            protected function present(Present $present)
            {
                $present->id();
                $present->integer('age');
                $present->string('fixed');
                $present->string('status');
                $present->string('empty');
                $present->timestamps();
            }
        })->setRawAttributes($attributes);
    }

    public function test_label_that_defined_fixed()
    {
        $record = $this->make(['fixed' => "Something Awesome"]);

        $this->assertSame("Doesn't matter what is value", $record->label('fixed'));
        $this->assertSame("Doesn't matter what is value", $record->fixed_label);
    }

    public function test_label_that_defined_with_parameters()
    {
        $record = $this->make();

        $this->assertSame("Default", $record->label('status', "Default"));
        $this->assertSame("Not Set", $record->status_label);
        $this->assertSame("Default", $record->status_label("Default"));
    }

    public function test_label_use_undefined()
    {
        $record = $this->make();

        $this->assertSame("Undefined Label", $record->label('empty'));
        $this->assertSame("Undefined Label", $record->empty_label);
    }

    public function test_label_empty_is_not_empty()
    {
        $record = $this->make(['empty' => "It's not empty"]);

        $this->assertSame("It's not empty", $record->label('empty'));
        $this->assertSame("It's not empty", $record->empty_label);
    }

    public function test_label_not_found()
    {
        $record = $this->make();

        $this->expectExceptionMessageMatches('/^Label \[undefined\] is not defined/');
        $record->undefined_label;
    }

    public function test_label_type_error()
    {
        $record = $this->make(['empty' => new \stdClass()]);

        $this->expectExceptionMessageMatches('/^Label \[empty\] in \[.*\] returned \[stdClass\], expected \[string\]$/');
        $record->empty_label;
    }

    public function test_label_of_objects()
    {
        $record = $this->make([
            'empty' => new class {
                public function getTranslatedLabel()
                {
                    return "This is from object label";
                }
            },
        ]);

        $this->assertSame("This is from object label", $record->empty_label);
    }

    public function test_label_of_fixed_alias()
    {
        $record = $this->make(['fixed' => "Something Awesome"]);

        $this->assertSame("Something Awesome", $record->label('fixedAlias'));
    }

    public function test_label_of_deep_objects()
    {
        $record = $this->make([
            'empty' => new class {
                public function getTranslatedLabel()
                {
                    return new class {
                        public function getTranslatedLabel()
                        {
                            return null;
                        }
                    };
                }
            },
        ]);

        $this->assertSame("Undefined Label", $record->empty_label);
    }

    public function test_label_of_objects_with_parameters()
    {
        $record = $this->make([
            'empty' => new class {
                public function getTranslatedLabel(string $foo)
                {
                    return $foo;
                }
            },
        ]);

        $this->assertSame("Foo", $record->label('empty', 'Foo'));
        $this->assertSame("Foo", $record->empty_label('Foo'));
    }

    public function test_label_of_default_timestamps()
    {
        $record = $this->make([
            'created_at' => new Carbon('2000/01/01 00:00:00'),
        ]);

        $this->assertSame('2000-01-01 00:00:00', $record->created_at_label);
    }

}