<?php

namespace Rapid\Laplus\Tests\Present;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\Concerns\HasPresentAttributes;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\TestCase;

class PresentCastingTest extends TestCase
{

    public function make(array $attributes = [])
    {
        return new class($attributes) extends Model
        {
            use HasPresent, HasPresentAttributes;

            protected function present(Present $present)
            {
                $present->integer('int');
                $present->dateTime('at');
                $present->text('days')->castUsing(
                    fn($value) => str_ends_with($value, ' Days') ? +substr($value, 0, -5) : 0,
                    fn($value) => "$value Days",
                );
                $present->attribute('at_year', get: fn(Model $model) => $model->at->year);
                $present->enum('some', SomeEnum::class);
            }
        };
    }

    public function test_primitive_type_casting()
    {
        $model = $this->make(['int' => '10', 'at' => '2000/01/01 12:00']);

        $this->assertSame(10, $model->int);
        $this->assertInstanceOf(Carbon::class, $model->at);
    }

    public function test_cast_using()
    {
        $model = $this->make()->setRawAttributes(['days' => '25 Days']);
        $this->assertSame(25, $model->days);

        $model = $this->make(['days' => 11]);
        $this->assertSame('11 Days', $model->getAttributes()['days']);
    }

    public function test_custom_attribute()
    {
        $model = $this->make()->setRawAttributes(['at' => '2022/01/01']);

        $this->assertSame(2022, $model->at_year);
    }

    public function test_enum_casting()
    {
        $model = $this->make()->setRawAttributes(['some' => 'First']);

        $this->assertSame(SomeEnum::First, $model->some);
    }

    public function test_auto_fillable()
    {
        $model = $this->make();

        $this->assertFalse(
            in_array('at_year', $model->getFillable()),
            "Assert 'at_year' out of fillable list, failed"
        );
    }

}