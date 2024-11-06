<?php

namespace Rapid\Laplus\Present\Attributes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Present\Present;

class MorphOneAttr extends Attribute
{

    public function __construct(
        public Model $related,
        public string $morphName,
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
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function getRelation(Model $model)
    {
        return $this->fireUsing(
            $model->morphOne($this->related::class, $this->morphName)
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
     * `fn (MorphOne $relation) => $relation`
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

    /**
     * Indicate that the relation is the latest single result of a larger morph relationship.
     *
     * @param  string|array|null  $column
     * @param  string|null  $relation
     * @return $this
     */
    public function latestOfMany($column = 'id', $relation = null)
    {
        return $this->using(fn(MorphOne $morph) => $morph->latestOfMany($column, $relation));
    }

    /**
     * Indicate that the relation is the oldest single result of a larger morph relationship.
     *
     * @param  string|array|null  $column
     * @param  string|null  $relation
     * @return $this
     */
    public function oldestOfMany($column = 'id', $relation = null)
    {
        return $this->using(fn(MorphOne $morph) => $morph->oldestOfMany($column, $relation));
    }

    /**
     * Indicate that the relation is a single result of a larger morph relationship.
     *
     * @param  Closure|string|array|null   $column
     * @param  string|Closure|null $aggregate
     * @param  string|null         $relation
     * @return $this
     */
    public function ofMany($column = 'id', $aggregate = 'MAX', $relation = null)
    {
        return $this->using(fn(MorphOne $morph) => $morph->ofMany(value($column), $aggregate, $relation));
    }

    /**
     * @inheritDoc
     */
    public function docblock(GuideScope $scope) : array
    {
        $doc = parent::docblock($scope);

        $doc[] = sprintf("@property %s<%s> %s()", $scope->typeHint(MorphOne::class), $scope->typeHint($this->related::class), $this->name);
        $doc[] = sprintf("@property ?%s \$%s", $scope->typeHint($this->related::class), $this->name);

        return $doc;
    }

}