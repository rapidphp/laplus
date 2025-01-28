<?php

namespace Rapid\Laplus\Present\Concerns;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\Attributes\FileColumn;
use Rapid\Laplus\Present\Types\File;

trait HasSelfFiles
{

    public static function bootHasSelfFile()
    {
        static::deleted(function (Model $record) {
            $record->file()->delete();
        });
    }

    /**
     * Get file value
     *
     * @return File
     */
    public function file()
    {
        $attr = $this->getPresent()->getAttribute('file');
        if ($attr instanceof FileColumn) {
            return $attr->getFileValue($this->getAttribute('file'), $this);
        }

        throw new \InvalidArgumentException("Attribute [file] should be a File");
    }

    /**
     * Get file disk name
     *
     * @return ?string
     */
    public static function getDiskName()
    {
        return static::attr('file', 'disk');
    }

    /**
     * Get the file url
     *
     * @return string|null
     */
    public function url()
    {
        return $this->file()->url();
    }

    /**
     * Get the file path
     *
     * @return string|null
     */
    public function path()
    {
        return $this->file()->path();
    }

}