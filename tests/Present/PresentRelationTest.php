<?php

namespace Rapid\Laplus\Tests\Present;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\Present\Model\ModelLaplusFirst;
use Rapid\Laplus\Tests\Present\Model\ModelLaplusSecond;
use Rapid\Laplus\Tests\Present\Models\Relations\Post;
use Rapid\Laplus\Tests\Present\Models\Relations\User;
use Rapid\Laplus\Tests\TestCase;

class PresentRelationTest extends TestCase
{

    public function test_belongs_to()
    {
        $model = new ModelLaplusFirst();

        $this->assertInstanceOf(BelongsTo::class, $model->second());
    }

    public function test_has_one()
    {
        $model = new ModelLaplusSecond();

        $this->assertInstanceOf(HasOne::class, $model->first());
    }

    public function test_has_many()
    {
        $model = new ModelLaplusSecond();

        $this->assertInstanceOf(HasMany::class, $model->firsts());
    }

}
