<?php

namespace Rapid\Laplus\Tests\Validation;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\HasTestingPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Support\Traits\HasRules;
use Rapid\Laplus\Tests\TestCase;
use Rapid\Laplus\Validation\PendingRules;

class ModifyingRuleTest extends TestCase
{
    public function testOnlyRules()
    {
        $model = new class extends Model {
            use HasTestingPresent, HasRules;

            protected function present(Present $present): void
            {
                $present->string('foo');
                $present->text('bar')->nullable();
                $present->string('string')->nullable();
                $present->float('float');
            }
        };

        /** @var PendingRules $rules */
        $rules = $model::rules();
        $this->assertSame([
            'foo' => ['required', 'string', 'max:255'],
            'string' => ['nullable', 'string', 'max:255'],
        ], $rules->only(['foo', 'string'])->toArray());
    }

    public function testExceptRules()
    {
        $model = new class extends Model {
            use HasTestingPresent, HasRules;

            protected function present(Present $present): void
            {
                $present->string('foo');
                $present->text('bar')->nullable();
                $present->string('string')->nullable();
                $present->float('float');
            }
        };

        /** @var PendingRules $rules */
        $rules = $model::rules();
        $this->assertSame([
            'bar' => ['nullable', 'string'],
            'float' => ['required', 'numeric'],
        ], $rules->except(['foo', 'string'])->toArray());
    }

    public function testFormattingRules()
    {
        $model = new class extends Model {
            use HasTestingPresent, HasRules;

            protected function present(Present $present): void
            {
                $present->integer('foo_test');
                $present->tinyInteger('barTest')->nullable();
                $present->string('my_string_column')->nullable();
            }
        };

        /** @var PendingRules $rules */
        $rules = $model::rules();
        $this->assertSame(
            ['custom:foo_test', 'custom:barTest', 'custom:my_string_column'],
            array_keys($rules->formatKey(static fn (string $key) => "custom:$key")->toArray()),
        );

        /** @var PendingRules $rules */
        $rules = $model::rules();
        $this->assertSame(
            ['fooTest', 'barTest', 'myStringColumn'],
            array_keys($rules->camelCase()->toArray()),
        );

        /** @var PendingRules $rules */
        $rules = $model::rules();
        $this->assertSame(
            ['foo_test', 'bar_test', 'my_string_column'],
            array_keys($rules->snakeCase()->toArray()),
        );

        /** @var PendingRules $rules */
        $rules = $model::rules();
        $this->assertSame(
            ['foo-test', 'bar-test', 'my-string-column'],
            array_keys($rules->kebabCase()->toArray()),
        );

        /** @var PendingRules $rules */
        $rules = $model::rules();
        $this->assertSame(
            ['FooTest', 'BarTest', 'MyStringColumn'],
            array_keys($rules->pascalCase()->toArray()),
        );

        /** @var PendingRules $rules */
        $rules = $model::rules();
        $this->assertSame(
            ['FOO_TEST', 'BARTEST', 'MY_STRING_COLUMN'],
            array_keys($rules->upperCase()->toArray()),
        );

        /** @var PendingRules $rules */
        $rules = $model::rules();
        $this->assertSame(
            ['footest', 'bartest', 'mystringcolumn'],
            array_keys($rules->camelCase()->lowerCase()->toArray()),
        );
    }
}