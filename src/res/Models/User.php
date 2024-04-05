<?php

namespace Rapid\Laplus\res\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Tests\Present\Models\Relations\Post;

class User extends Model
{
    use HasFactory;
    use HasPresent;

    protected function getPresent() : Present
    {
        return new class($this) extends Present
        {
            protected function present()
            {
                $this->id();
                $this->text('name');
                $this->text('user_name');
            }
        };
    }

}