<?php

namespace Rapid\Laplus\Present\Concerns;

use Illuminate\Support\Facades\Storage;

trait HasFactoryPresents
{

    public function withFileCopy(string $attribute, string $fromFullPath, ?string $to = null)
    {
        return [$attribute => $this->fileCopy($attribute, $fromFullPath, $to)];
    }

    public function fileCopy(string $attribute, string $fromFullPath, ?string $to = null)
    {
        if (is_null($to)) {
            $extension = @pathinfo($fromFullPath, PATHINFO_EXTENSION);
            $to = md5(rand(0, PHP_INT_MAX) . '-' . time()) . ($extension ? '.' . $extension : '');
        }

        $toDisk = $this->modelName()::attr($attribute, 'disk');
        $toFullPath = Storage::disk($toDisk)->path($to);

        copy($fromFullPath, $toFullPath);

        return $to;
    }

    public function withFileMove(string $attribute, string $fromFullPath, ?string $to = null)
    {
        return [$attribute => $this->fileMove($attribute, $fromFullPath, $to)];
    }

    public function fileMove(string $attribute, string $fromFullPath, ?string $to = null)
    {
        if (is_null($to)) {
            $extension = @pathinfo($fromFullPath, PATHINFO_EXTENSION);
            $to = md5(rand(0, PHP_INT_MAX) . '-' . time()) . ($extension ? '.' . $extension : '');
        }

        $toDisk = $this->modelName()::attr($attribute, 'disk');
        $toFullPath = Storage::disk($toDisk)->path($to);

        rename($fromFullPath, $toFullPath);

        return $to;
    }

}