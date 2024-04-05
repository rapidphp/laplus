<?php

namespace Rapid\Laplus\Present\Attributes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\Present;

class HasOne extends Attribute
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function getRelation(Model $model)
    {
        return $this->fireUsing(
            $model->hasOne($this->related::class, $this->foreignKey, $this->localKey)
                ->withDefault($this->withDefault),
            $model
        );
    }



    protected $withDefault = false;

    /**
     * Set default value
     *
     * @param array|Closure|bool $callback
     * @return $this
     */
    public function withDefault(array|Closure|bool $callback = true)
    {
        $this->withDefault = $callback;
        return $this;
    }


    protected array $using = [];

    /**
     * Fire callback when creating relation
     *
     * `fn (HasOne $relation) => $relation`
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