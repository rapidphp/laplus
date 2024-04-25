<?php

namespace Rapid\Laplus\Present\Attributes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Rapid\Laplus\Present\Present;

class SlugColumn extends Column
{

    /**
     * Boots the attribute
     *
     * @param Present $present
     * @return void
     */
    public function boot(Present $present)
    {
        parent::boot($present);

        $present->instance::creating(function (Model $model)
        {
            if ($model->getAttribute($this->name) === null)
            {
                if (null !== $value = $model->getAttribute($this->used))
                {
                    $model->setAttribute($this->name, $this->createSlug($value, $model::class));
                }
            }
        });

        $present->instance::updating(function (Model $model)
        {
            if ($model->getAttribute($this->name) === null || ($model->wasChanged($this->used) && !$model->wasChanged($this->name)))
            {
                if (null !== $value = $model->getAttribute($this->used))
                {
                    $model->setAttribute($this->name, $this->createSlug($value, $model::class, $model));
                }
            }
        });
    }

    /**
     * Create slug
     *
     * @param string      $value
     * @param string|null $model
     * @param Model|null  $expect
     * @return string
     */
    protected function createSlug(string $value, string $model = null, Model $expect = null)
    {
        // Create slug
        $slug = Str::slug($value, language: null);

        // Normal column
        if (!$this->isUnique)
        {
            return $slug;
        }

        // Accept if slug not exists
        if (
            $expect ?
                !$model::where($this->name, $slug)->where('id', '!=', $expect->id)->exists() :
                !$model::where($this->name, $slug)->exists()
        )
        {
            return $slug;
        }

        // Found maximum number for same slugs
        $likes = ($expect ? $model::where('id', '!=', $expect->id) : $model::query())
            ->where($this->name, 'regexp', '^' . preg_quote($slug) . '\-')->pluck($this->name);

        $max = 1;
        foreach ($likes as $like)
        {
            $like = substr($like, strlen($slug) + 1);
            if (is_numeric($like) && $like > $max)
            {
                $max = $like;
            }
        }

        // Create slug with new number
        return $slug . '-' . ++$max;
    }

    protected string $used = 'title';

    /**
     * Set used column to create slug
     *
     * @param string $column
     * @return $this
     */
    public function use(string $column)
    {
        $this->used = $column;

        return $this;
    }

    /**
     * Column is unique
     *
     * @var bool
     */
    protected bool $isUnique = false;

    /**
     * Make slug column unique
     *
     * @param bool|string|null $indexName
     * @return SlugColumn
     */
    public function unique(bool|string $indexName = null)
    {
        $this->isUnique = true;

        return parent::unique($indexName);
    }

}