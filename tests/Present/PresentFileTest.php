<?php

namespace Rapid\Laplus\Tests\Present;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Rapid\Laplus\Present\Concerns\HasColumnFiles;
use Rapid\Laplus\Present\Concerns\HasSelfFiles;
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
            use HasPresent, HasColumnFiles;

            protected function present(Present $present)
            {
                $present->id();
                $present->file('image')->diskPublic()->url(fn($model, $name) => "localhost/{$name}");
            }
        };
    }

    public function test_file()
    {
        $test = $this->make()->setRawAttributes([
            'id' => 12,
            'image' => 'test.png',
        ]);

        $this->assertIsString($test->image);
        $this->assertInstanceOf(File::class, $test->file('image'));

        $this->assertSame(Storage::disk('public')->path('test.png'), $test->file('image')->path());
        $this->assertSame("localhost/test.png", $test->file('image')->url());
    }

    public function make_self()
    {
        return new class extends Model
        {
            use HasPresent, HasSelfFiles;

            protected function present(Present $present)
            {
                $present->id();
                $present->file('file')->diskPublic()->url(fn($model, $name) => "localhost/{$name}");
            }
        };
    }

    public function test_self_file()
    {
        $test = $this->make_self()->setRawAttributes([
            'id' => 12,
            'file' => 'test.png',
        ]);

        $this->assertIsString($test->file);
        $this->assertInstanceOf(File::class, $test->file());

        $this->assertSame(Storage::disk('public')->path('test.png'), $test->path());
        $this->assertSame("localhost/test.png", $test->url());
    }

}