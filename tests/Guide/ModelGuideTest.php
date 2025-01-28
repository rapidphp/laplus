<?php

namespace Rapid\Laplus\Tests\Guide;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Guide\Attributes\IsAttribute;
use Rapid\Laplus\Guide\Attributes\IsRelation;
use Rapid\Laplus\Guide\Guides\TestGuide;
use Rapid\Laplus\Guide\ModelAuthor;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide;
use Rapid\Laplus\Tests\TestCase;

class ModelGuideTest extends TestCase
{

    public function test_guide_generation()
    {
        $guide = new TestGuide;
        $guide->run(new ModelAuthor($guide, _TestModel1ForGuide::class));

        $guide->assertSame([
            '@property int $id',
            '@property string $name',
            '@property float $money',
            '@property array $friends List of friends',
            '@property-read string $name_label Test name',
            '@method string name_label() Test name',
            '@property-read string $money_label Money Test',
            '@method string money_label(string $currency = "$") Money Test',
            '@method string friends_label(int $max)',
            '@property-read string $created_at_label',
            '@method string created_at_label()',
            '@property-read string $updated_at_label',
            '@method string updated_at_label()',
        ]);
    }

    public function test_generate_custom_hint()
    {
        $class = new class extends Model {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->string('class')
                    ->typeHint('class-string')
                    ->docHint('Target Class');
            }
        };

        $guide = new TestGuide;
        $guide->run(new ModelAuthor($guide, get_class($class)));

        $guide->assertSame([
            '@property class-string $class Target Class',
        ]);
    }

    public function test_generate_relations()
    {
        $class = new class extends Model {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->belongsTo(_TestModel1ForGuide::class, 'testBel');
                $present->hasOne(_TestModel1ForGuide::class, 'testOne');
                $present->hasMany(_TestModel1ForGuide::class, 'testMany');
                $present->belongsToMany(_TestModel1ForGuide::class, 'testBelMany');
                $present->morphs('first_morph');
                $present->morphs('second_morph')->types([_TestModel1ForGuide::class, ModelGuideTest::class]);
                $present->morphsId('third_morph');
            }
        };

        $guide = new TestGuide;
        $guide->run(new ModelAuthor($guide, get_class($class)));

        $guide->assertSame([
            '@property int $testBel_id',
            '@method \Illuminate\Database\Eloquent\Relations\BelongsTo<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> testBel()',
            '@property-read ?\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide $testBel',
            '@method \Illuminate\Database\Eloquent\Relations\HasOne<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> testOne()',
            '@property-read ?\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide $testOne',
            '@method \Illuminate\Database\Eloquent\Relations\HasMany<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> testMany()',
            '@property-read \Illuminate\Database\Eloquent\Collection<int, \Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> $testMany',
            '@method \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> testBelMany()',
            '@property-read \Illuminate\Database\Eloquent\Collection<int, \Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> $testBelMany',
            '@method \Illuminate\Database\Eloquent\Relations\MorphTo first_morph()',
            '@property-read null|\Illuminate\Database\Eloquent\Model $first_morph',
            '@property string $first_morph_type',
            '@property int|string $first_morph_id',
            '@method \Illuminate\Database\Eloquent\Relations\MorphTo second_morph()',
            '@property-read null|\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide|\Rapid\Laplus\Tests\Guide\ModelGuideTest $second_morph',
            '@property string $second_morph_type',
            '@property int|string $second_morph_id',
        ]);
    }

    public function test_generate_model_attributes()
    {
        $class = new class extends Model {

            /**
             * Full Name
             * @return string
             */
            public function getFullNameAttribute(): string
            {
                return '';
            }

            /**
             * Not Resolved
             *
             * @param string $value
             * @return void
             */
            public function setFullNameAttribute(string $value)
            {
            }

            /**
             * Foo
             *
             * @param bool $value
             * @return void
             */
            public function setFooAttribute(bool $value): void
            {
            }

            /**
             * Bar
             *
             * @return int
             */
            public function getBarAttribute(): int
            {
                return 0;
            }

        };

        $guide = new TestGuide;
        $guide->run(new ModelAuthor($guide, get_class($class)));

        $guide->assertSame([
            '@property string $full_name Full Name',
            '@property string $fullName Full Name',
            '@property-write bool $foo Foo',
            '@property-read int $bar Bar',
        ]);
    }

    public function test_generate_model_modern_attributes()
    {
        $class = new class extends Model {

            /**
             * Full Name
             */
            #[IsAttribute('string')]
            public function fullName(): Attribute
            {
                return Attribute::make(
                    get: fn() => null,
                    set: fn() => null,
                );
            }

            /**
             * Foo
             *
             * @return Attribute
             */
            #[IsAttribute(['null', 'int', _TestModel1ForGuide::class])]
            public function foo(): Attribute
            {
                return Attribute::set(fn() => null);
            }

            /**
             * Bar
             *
             * @return Attribute
             */
            #[IsAttribute]
            public function bar(): Attribute
            {
                return Attribute::get(fn() => null);
            }

        };

        $guide = new TestGuide;
        $guide->run(new ModelAuthor($guide, get_class($class)));

        $guide->assertSame([
            '@property string $fullName Full Name',
            '@property string $full_name Full Name',
            '@property-write null|int|\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide $foo Foo',
            '@property-read mixed $bar Bar',
        ]);
    }

    public function test_generate_attributes()
    {
        $class = new class extends Model {

            /**
             * Test Relationship
             */
            #[IsRelation]
            public function test()
            {
                return $this->belongsTo(_TestModel1ForGuide::class);
            }

            #[IsRelation]
            public function testMany()
            {
                return $this->hasMany(_TestModel1ForGuide::class);
            }

            #[IsRelation([_TestModel1ForGuide::class, ModelGuideTest::class])]
            public function testMorph()
            {
                return $this->morphTo();
            }

        };

        $guide = new TestGuide;
        $guide->run(new ModelAuthor($guide, get_class($class)));

        $guide->assertSame([
            '@property-read null|\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide $test Test Relationship',
            '@property-read \Illuminate\Database\Eloquent\Collection<int, \Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> $testMany',
            '@property-read null|\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide|\Rapid\Laplus\Tests\Guide\ModelGuideTest $testMorph',
        ]);
    }

    public function test_generate_default_morphs()
    {
        $class = new class extends Model {

            #[IsRelation]
            public function test()
            {
                return $this->morphTo();
            }

        };

        $guide = new TestGuide;
        $guide->run(new ModelAuthor($guide, get_class($class)));

        $guide->assertSame([
            '@property-read null|\Illuminate\Database\Eloquent\Model $test',
        ]);
    }

    public function test_generate_morphs()
    {
        $class = new class extends Model {

            #[IsRelation(['A', 'B'])]
            public function test()
            {
                return $this->morphTo();
            }

        };

        $guide = new TestGuide;
        $guide->run(new ModelAuthor($guide, get_class($class)));

        $guide->assertSame([
            '@property-read null|A|B $test',
        ]);
    }

}