<?php

namespace Rapid\Laplus\Present\Attributes;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\Present;

class MorphToManyAttr extends Attribute
{

    public function __construct(
        public Model $related,
        public string $morphName,
        string $relationName,
        public Model $pivot,
        public ?string $foreignPivotKey = null,
        public ?string $relatedPivotKey = null,
        public ?string $parentKey = null,
        public ?string $relatedKey = null,
        public bool $inverse = false,
    )
    {
        parent::__construct($relationName);
    }

    /**
     * Boots relationship
     *
     * @param Present $present
     * @return void
     */
    public function boot(Present $present)
    {
        parent::boot($present);

        $present->instance::resolveRelationUsing($this->name, $this->getRelation(...));
    }

    /**
     * Get relation value
     *
     * @param Model $model
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function getRelation(Model $model)
    {
        return $this->fireUsing(
            $model->morphToMany(
                $this->related::class, $this->morphName, $this->pivot?->getTable(),
                $this->foreignPivotKey, $this->relatedPivotKey, $this->parentKey,
                $this->relatedKey, $this->name,
                $this->inverse,
            ),
            $model
        );
    }



    protected array $using = [];

    /**
     * Fire callback when creating relation
     *
     * `fn (MorphToMany $relation) => $relation`
     *
     * @param $callback
     * @return $this
     */
    public function using($callback)
    {
        $this->using[] = $callback;
        return $this;
    }

    /**
     * Fire using callbacks
     *
     * @param       $arg
     * @param Model $model
     * @return mixed
     */
    protected function fireUsing($arg, Model $model)
    {
        foreach ($this->using as $callback)
        {
            $arg = $callback($arg, $model);
        }

        return $arg;
    }

}