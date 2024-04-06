<?php

namespace Rapid\Laplus\Present\Attributes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\Present;

class HasManyAttr extends Attribute
{

    public function __construct(
        public Model $related,
        public string $foreignKey,
        public string $localKey,
        string $relationName,
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
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getRelation(Model $model)
    {
        return $this->fireUsing(
            $model->hasMany($this->related::class, $this->foreignKey, $this->localKey),
            $model
        );
    }




    protected array $using = [];

    /**
     * Fire callback when creating relation
     *
     * `fn (HasMany $relation) => $relation`
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