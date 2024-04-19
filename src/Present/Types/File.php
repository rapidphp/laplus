<?php

namespace Rapid\Laplus\Present\Types;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File as FileManager;
use Illuminate\Support\Facades\Storage;
use JsonSerializable;
use Rapid\Laplus\Present\Attributes\FileColumn;

class File
{

    public function __construct(
        public ?string $name,
        public Model $model,
        public FileColumn $column,
    )
    {
    }

    /**
     * Check file visibility
     *
     * @return bool
     */
    public function isVisible()
    {
        if ($this->isEmpty())
        {
            return false;
        }

        if ($disk = $this->column->getDisk())
        {
            return @config()->get('filesystems.disks')[$disk]['visibility'] == 'public';
        }

        return false;
    }

    /**
     * Abort on invisible file
     *
     * @return void
     */
    public function abortVisibility()
    {
        if (!$this->isVisible())
        {
            abort(403);
        }
    }

    /**
     * Get file download url
     *
     * @return string|null
     */
    public function url()
    {
        if ($this->isEmpty() || !$this->isVisible())
        {
            return null;
        }

        if ($callback = $this->column->getUrlUsing())
        {
            return $callback($this->model, $this->name);
        }

        if ($disk = $this->column->getDisk())
        {
            if ($prefix = @config()->get('filesystems.disks')[$disk]['url'])
            {
                return $prefix . '/' . $this->name;
            }
        }

        throw new \InvalidArgumentException("The file not supported url!");
    }

    /**
     * Check has file
     *
     * @return bool
     */
    public function hasFile()
    {
        return isset($this->name);
    }

    /**
     * Check value is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return is_null($this->name);
    }

    /**
     * Get the file path
     *
     * @return string|null
     */
    public function path()
    {
        if ($this->isEmpty())
        {
            return null;
        }

        if ($disk = $this->column->getDisk())
        {
            return Storage::disk($disk)->path($this->name);
        }

        throw new \InvalidArgumentException("The file not supported path!");
    }

    /**
     * Read the file contents
     *
     * @return string|null
     */
    public function read()
    {
        if ($this->isEmpty())
        {
            return null;
        }

        return FileManager::get($this->path());
    }

    /**
     * Write contents to the file
     *
     * @param string $contents
     * @return bool|int
     */
    public function write(string $contents)
    {
        if ($this->isEmpty())
        {
            return false;
        }

        return FileManager::put($this->path(), $contents);
    }

    /**
     * Delete the file
     *
     * @return bool
     */
    public function delete()
    {
        if ($this->isEmpty())
        {
            return false;
        }

        return FileManager::delete($this->path());
    }


    /**
     * Create a download response
     *
     * @param string|null $name
     * @param array       $headers
     * @param string|null $disposition
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download(?string $name = null, array $headers = [], ?string $disposition = 'attachment')
    {
        if ($this->isEmpty())
        {
            abort(404);
        }

        $this->abortVisibility();
        
        return response()->download($this->path(), $name, $headers, $disposition);
    }

    /**
     * Create a show response
     *
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function show(array $headers = [])
    {
        if ($this->isEmpty())
        {
            abort(404);
        }

        $this->abortVisibility();

        return response()->file($this->path(), $headers);
    }

}