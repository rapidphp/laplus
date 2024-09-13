<?php

namespace Rapid\Laplus\Present\Concerns;

use Rapid\Laplus\Present\Attributes\FileColumn;
use Rapid\Laplus\Present\Types\File;

trait HasColumnFiles
{

    /**
     * Get file value
     *
     * @param string $attribute
     * @return File
     */
    public function file(string $attribute)
    {
        $attr = $this->getPresent()->getAttribute($attribute);
        if ($attr instanceof FileColumn)
        {
            return $attr->getFileValue($this->getAttribute($attribute), $this);
        }

        throw new \InvalidArgumentException("Attribute [{$attribute}] should be a File");
    }

    /**
     * Get file disk name
     *
     * @param string $attribute
     * @return ?string
     */
    public static function getDiskName(string $attribute)
    {
        return static::attr($attribute, 'disk');
    }

}