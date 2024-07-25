<?php

namespace Rapid\Laplus\Tests\Present;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Present\Types\File;
use Rapid\Laplus\Tests\TestCase;

class PresentFileTest extends TestCase
{

    public function make()
    {
        return new class extends Model
        {
            use HasPresent;

            protected function present(Present $present)
            {
                $present->id();
                $present->file('image')->diskPublic()->url(fn($model) => "localhost/{$model->image->name}");
            }
        };
    }

    public function test_file()
    {
        $test = $this->make()->setRawAttributes([
            'id' => 12,
            'image' => 'test.png',
        ]);

        $this->assertInstanceOf(File::class, $test->image);

        $this->assertSame(Storage::disk('public')->path('test.png'), $test->file('image')->path());
        $this->assertSame("localhost/test.png", $test->image->url());
    }

}