<?php

namespace Rapid\Laplus\Tests\Guide;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Guide\ModelGuide;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\Guide\Models\_TestLabel1ForGuide;
use Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide;
use Rapid\Laplus\Tests\TestCase;

class ModelGuideTest extends TestCase
{

    public function test_guide_generation()
    {
        $guide = new ModelGuide(_TestModel1ForGuide::class);

        $guide->assertInsertComment(
            <<<'COMMENT'
            /**
             * @GuidePresent
             * @property int $id
             * @property string $name
             * @property float $money
             * @property array $friends List of friends
             * @property string $name_label Test name
             * @method string name_label() Test name
             * @property string $money_label Money Test
             * @method string money_label(string $currency = "$") Money Test
             * @method string friends_label(int $max)
             * @property string $created_at_label
             * @method string created_at_label()
             * @property string $updated_at_label
             * @method string updated_at_label()
             * @EndGuidePresent
             */
            COMMENT
        );
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

        $guide = new ModelGuide(
            get_class($class),
        );

        $guide->assertInsertComment(
            <<<'COMMENT'
            /**
             * @GuidePresent
             * @property class-string $class Target Class
             * @EndGuidePresent
             */
            COMMENT
        );
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

        $guide = new ModelGuide(
            get_class($class),
        );

        $guide->assertInsertComment(
            <<<'COMMENT'
            /**
             * @GuidePresent
             * @property int $testBel_id
             * @property \Illuminate\Database\Eloquent\Relations\BelongsTo<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> testBel()
             * @property \Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide $testBel
             * @property \Illuminate\Database\Eloquent\Relations\HasOne<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> testOne()
             * @property \Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide $testOne
             * @property \Illuminate\Database\Eloquent\Relations\HasMany<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> testMany()
             * @property \Illuminate\Database\Eloquent\Collection<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> $testMany
             * @property \Illuminate\Database\Eloquent\Relations\BelongsToMany<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> testBelMany()
             * @property \Illuminate\Database\Eloquent\Collection<\Rapid\Laplus\Tests\Guide\Models\_TestModel1ForGuide> $testBelMany
             * @EndGuidePresent
             */
            COMMENT
        );
    }

}