<?php

namespace Rapid\Laplus\Tests\Guide;

use Illuminate\Database\Eloquent\Model;
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
            '@property string $name_label Test name',
            '@method string name_label() Test name',
            '@property string $money_label Money Test',
            '@method string money_label(string $currency = "$") Money Test',
            '@method string friends_label(int $max)',
            '@property string $created_at_label',
            '@method string created_at_label()',
            '@property string $updated_at_label',
            '@method string updated_at_label()',
        ]);
    }

    public function test_generate_custom_hint()
    {
        $class = new class extends Model
        {
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
        $class = new class extends Model
        {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->belongsTo(_TestModel1ForGuide::class, 'testBel');
                $present->hasOne(_TestModel1ForGuide::class, 'testOne');
                $present->hasMany(_TestModel1ForGuide::class, 'testMany');
                $present->belongsToMany(_TestModel1ForGuide::class, 'testBelMany');
            }
        };

        $guide = new TestGuide;
        $guide->run(new ModelAuthor($guide, get_class($class)));

        $guide->assertSame([
            '@property int $testBel_id',
            '@property \Illuminate\Database\Eloquent\Relations\BelongsTo<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> testBel()',
            '@property ?\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide $testBel',
            '@property \Illuminate\Database\Eloquent\Relations\HasOne<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> testOne()',
            '@property ?\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide $testOne',
            '@property \Illuminate\Database\Eloquent\Relations\HasMany<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> testMany()',
            '@property \Illuminate\Database\Eloquent\Collection<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> $testMany',
            '@property \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> testBelMany()',
            '@property \Illuminate\Database\Eloquent\Collection<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> $testBelMany',
        ]);
    }

    public function test_generate_model_attributes()
    {
        $class = new class extends Model
        {

            public function getFullNameAttribute() : string
            {
                return '';
            }

            public function setFullNameAttribute(string $value)
            {
            }

            public function setFooAttribute(bool $value) : void
            {
            }

            public function getBarAttribute() : int
            {
                return 0;
            }

        };

        $guide = new TestGuide;
        $guide->run(new ModelAuthor($guide, get_class($class)));

        $guide->assertSame([
            '@property string $full_name',
            '@property bool $foo',
            '@property int $bar',
        ]);
    }

    public function test_generate_attributes()
    {
        $class = new class extends Model
        {

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

        };

        $guide = new TestGuide;
        $guide->run(new ModelAuthor($guide, get_class($class)));

        $guide->assertSame([
            '@property ?\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide $test',
            '@property \Illuminate\Database\Eloquent\Collection<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> $testMany',
        ]);
    }

}