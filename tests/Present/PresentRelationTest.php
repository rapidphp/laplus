<?php

namespace Rapid\Laplus\Tests\Present;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Rapid\Laplus\Tests\Present\Models\Relations\Post;
use Rapid\Laplus\Tests\Present\Models\Relations\User;
use Rapid\Laplus\Tests\TestCase;

class PresentRelationTest extends TestCase
{

    public function test_belongs_to()
    {
        $post = Post::create([
            'user_id' => 123,
        ]);

        $this->assertInstanceOf(BelongsTo::class, $post->user());
    }

    public function test_has_many()
    {
        $user = new User();

        $this->assertInstanceOf(HasMany::class, $user->posts());
    }
    
}