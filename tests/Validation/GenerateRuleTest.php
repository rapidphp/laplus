<?php

namespace Rapid\Laplus\Tests\Validation;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;
use Rapid\Laplus\Present\HasTestingPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Support\Traits\HasRules;
use Rapid\Laplus\Tests\Present\Model\ModelLaplusFirst;
use Rapid\Laplus\Tests\TestCase;
use Rapid\Laplus\Validation\PendingRules;

class GenerateRuleTest extends TestCase
{
    public function testEmptyRules()
    {
        $model = new class extends Model {
            use HasTestingPresent, HasRules;

            protected function present(Present $present): void
            {
            }
        };

        /** @var PendingRules $rules */
        $rules = $model::rules();

        $this->assertEmpty($rules->toArray());
    }

    public function testPrimitiveRules()
    {
        $model = new class extends Model {
            use HasTestingPresent, HasRules;

            protected function present(Present $present): void
            {
                $present->integer('integer');
                $present->unsignedInteger('unsignedInteger')->nullable();
                $present->tinyInteger('tinyInteger')->nullable();
                $present->unsignedTinyInteger('unsignedTinyInteger');
                $present->smallInteger('smallInteger');
                $present->unsignedSmallInteger('unsignedSmallInteger');
                $present->mediumInteger('mediumInteger');
                $present->unsignedMediumInteger('unsignedMediumInteger');
                $present->bigInteger('bigInteger');
                $present->unsignedBigInteger('unsignedBigInteger');

                $present->string('string')->nullable();
                $present->string('string2', 2048)->nullable();
                $present->float('float');
                $present->boolean('bool');
                $present->decimal('decimal', 10, 4);
                $present->text('text');
            }
        };

        /** @var PendingRules $rules */
        $rules = $model::rules();

        $this->assertSame([
            'integer' => ['required', 'int', 'min:-2147483648', 'max:2147483647'],
            'unsignedInteger' => ['nullable', 'int', 'min:0', 'max:4294967295'],
            'tinyInteger' => ['nullable', 'int', 'min:-128', 'max:127'],
            'unsignedTinyInteger' => ['required', 'int', 'min:0', 'max:255'],
            'smallInteger' => ['required', 'int', 'min:-32768', 'max:32767'],
            'unsignedSmallInteger' => ['required', 'int', 'min:0', 'max:65535'],
            'mediumInteger' => ['required', 'int', 'min:8388607', 'max:-8388608'],
            'unsignedMediumInteger' => ['required', 'int', 'min:0', 'max:16777215'],
            'bigInteger' => ['required', 'int', 'min:-9223372036854775808', 'max:9223372036854775807'],
            'unsignedBigInteger' => ['required', 'int', 'min:0', 'max:18446744073709551615'],
            'string' => ['nullable', 'string', 'max:255'],
            'string2' => ['nullable', 'string', 'max:2048'],
            'float' => ['required', 'numeric'],
            'bool' => ['required', 'boolean'],
            'decimal' => ['required', 'numeric', 'digits:6', 'decimal:4'],
            'text' => ['required', 'string'],
        ], $rules->toArray());
    }

    public function testIgnoreNotFillableRules()
    {
        $model = new class extends Model {
            use HasTestingPresent, HasRules;

            protected function present(Present $present): void
            {
                $present->id();
                $present->string('foo');
                $present->string('bar')->nullable()->notFillable();
                $present->text('text')->notFillable();
            }
        };

        /** @var PendingRules $rules */
        $rules = $model::rules();

        $this->assertSame([
            'foo' => ['required', 'string', 'max:255'],
        ], $rules->toArray());
    }

    public function testUniqueRules()
    {
        $model = new class extends Model {
            use HasTestingPresent, HasRules;

            protected function present(Present $present): void
            {
                $present->id();
                $present->string('foo')->unique();
            }
        };

        $this->assertSame(
            serialize(Rule::unique($model::class)),
            serialize(Arr::first(
                $model::rules()->toArray()['foo'],
                fn($rule) => $rule instanceof Unique,
            )),
        );

        $this->assertSame(
            serialize(Rule::unique($model::class)),
            serialize(Arr::first(
                $model::rules()->forCreate()->toArray()['foo'],
                fn($rule) => $rule instanceof Unique,
            )),
        );

        $this->assertSame(
            serialize(Rule::unique($model::class)->ignore($model)),
            serialize(Arr::first(
                $model::rules()->forUpdate($model)->toArray()['foo'],
                fn($rule) => $rule instanceof Unique,
            )),
        );

        $this->assertSame(
            serialize(Rule::exists($model::class)),
            serialize(Arr::first(
                $model::rules()->forFind()->toArray()['foo'],
                fn($rule) => $rule instanceof Exists,
            )),
        );
    }

    public function testBelongsToRules()
    {
        $model = new class extends Model {
            use HasTestingPresent, HasRules;

            protected function present(Present $present): void
            {
                $present->belongsTo(ModelLaplusFirst::class, 'foo', 'foo_id');
            }
        };

        $this->assertSame(
            serialize(Rule::exists(ModelLaplusFirst::class)),
            serialize(Arr::first(
                $model::rules()->toArray()['foo_id'],
                fn($rule) => $rule instanceof Exists,
            )),
        );
    }

    public function testCustomColumnRules()
    {
        $model = new class extends Model {
            use HasTestingPresent, HasRules;

            protected function present(Present $present): void
            {
                $present->string('foo')->rules(['foo_rule']);
                $present->string('bar')->dataTypeRules(['bar_rule']);
                $present->string('foo_callable')->rules(static fn() => ['foo_rule']);
                $present->string('bar_callable')->dataTypeRules(static fn() => ['bar_rule']);
            }
        };

        $this->assertSame(
            [
                'foo' => ['foo_rule'],
                'bar' => ['required', 'bar_rule'],
                'foo_callable' => ['foo_rule'],
                'bar_callable' => ['required', 'bar_rule'],
            ],
            $model::rules()->toArray(),
        );
    }

    public function testCustomRules()
    {
        $model = new class extends Model {
            use HasTestingPresent, HasRules;

            protected function present(Present $present): void
            {
                $present->string('foo');

                $present->rules(static fn() => [
                    'foo' => ['foo_rule'],
                ]);
            }
        };

        $this->assertSame(
            [
                'foo' => ['foo_rule'],
            ],
            $model::rules()->toArray(),
        );
    }
}