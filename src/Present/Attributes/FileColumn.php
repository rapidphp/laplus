<?php

namespace Rapid\Laplus\Present\Attributes;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Present\Types\File;

class FileColumn extends Column
{

    public function __construct(string $name)
    {
        parent::__construct($name, 'text', [$name]);

        // $this->castUsing(
        //     get: $this->getFileValue(...),
        //     set: $this->setFileValue(...),
        // );
    }

    /**
     * Boots the file column
     *
     * @param Present $present
     * @return void
     */
    public function boot(Present $present)
    {
        parent::boot($present);

        if ($this->deleteOnDelete)
        {
            $present->instance::deleted(function (Model $model)
            {
                $model->getAttribute($this->name)->delete();
            });
        }

        // if (isset($this->withNameAttribute))
        // {
        //     $present->getters[$this->withNameAttribute] = function ($value, Model $model)
        //     {
        //         $file = $model->getAttribute($this->name);
        //         if (is_object($file))
        //         {
        //             return $file->name;
        //         }
        //
        //         return (string) $file;
        //     };
        //
        //     $present->setters[$this->withNameAttribute] = function ($value, Model $model)
        //     {
        //         $model->setAttribute($this->name, $value);
        //     };
        // }
    }


    protected $urlUsing;

    /**
     * Set custom url using callback
     *
     * `fn (Model $model, string $name) => "url"`
     *
     * @param callable $callback
     * @return $this
     */
    public function url(callable $callback)
    {
        $this->urlUsing = $callback;
        return $this;
    }

    /**
     * Set custom route as the file url
     *
     * Route should contain a model id as a parameter
     *
     * @param string $route
     * @return $this
     */
    public function urlRoute(string $route)
    {
        return $this->url(fn(Model $model) => route($route, $model));
    }

    public function getUrlUsing()
    {
        return $this->urlUsing;
    }


    // protected ?string $withNameAttribute = null;
    //
    // /**
    //  * Add file name attribute to access the string file value.
    //  *
    //  * @param string|null $attribute
    //  * @return $this
    //  */
    // public function withName(string $attribute = null)
    // {
    //     $this->withNameAttribute = $attribute ?? $this->name . '_name';
    //
    //     return $this;
    // }



    protected ?string $disk = null;

    /**
     * Set file disk
     *
     * @param string $disk
     * @return $this
     */
    public function disk(string $disk)
    {
        $this->disk = $disk;
        return $this;
    }

    /**
     * Set file disk to public disk
     *
     * @return $this
     */
    public function diskPublic()
    {
        return $this->disk('public');
    }

    public function getDisk()
    {
        return $this->disk;
    }


    protected bool $deleteOnDelete = false;

    public function deleteOnDelete()
    {
        $this->deleteOnDelete = true;
        return $this;
    }


    public function getFileValue($value, Model $model)
    {
        return new File($value, $model, $this);
    }

    public function setFileValue($value)
    {
        if ($value instanceof File)
        {
            return $value->name;
        }

        return $value;
    }

}
