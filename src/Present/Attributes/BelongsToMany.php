<?php

namespace Rapid\Laplus\Present\Attributes;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\Present;

class BelongsToMany extends Attribute
{

    public function __construct(
        public Model $related,
        public ?string $table = null,
        public ?string $foreignPivotKey = null,
        public ?string $relatedPivotKey = null,
        public ?string $parentKey = null,
        public ?string $relatedKey = null,
        string $relationName = '',
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function getRelation(Model $model)
    {
        return $this->fireUsing(
            $model->belongsToMany($this->related::class, $this->table, $this->foreignPivotKey, $this->relatedPivotKey, $this->parentKey, $this->relatedKey, $this->name),
            $model
        );
    }



    protected array $using = [];

    /**
     * Fire callback when creating relation
     *
     * `fn (BelongsToMany $relation) => $relation`
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