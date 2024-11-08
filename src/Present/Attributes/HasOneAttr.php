<?php

namespace Rapid\Laplus\Present\Attributes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Present\Present;

class HasOneAttr extends Attribute
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


    /**
     * Indicate that the relation is the latest single result of a larger one-to-many relationship.
     *
     * @param  string|array|null  $column
     * @param  string|null  $relation
     * @return $this
     */
    public function latestOfMany($column = 'id', $relation = null)
    {
        return $this->using(fn(HasOne $hasOne) => $hasOne->latestOfMany($column, $relation));
    }

    /**
     * Indicate that the relation is the oldest single result of a larger one-to-many relationship.
     *
     * @param  string|array|null  $column
     * @param  string|null  $relation
     * @return $this
     */
    public function oldestOfMany($column = 'id', $relation = null)
    {
        return $this->using(fn(HasOne $hasOne) => $hasOne->oldestOfMany($column, $relation));
    }

    /**
     * Indicate that the relation is a single result of a larger one-to-many relationship.
     *
     * @param  Closure|string|array|null   $column
     * @param  string|Closure|null $aggregate
     * @param  string|null         $relation
     * @return $this
     */
    public function ofMany($column = 'id', $aggregate = 'MAX', $relation = null)
    {
        return $this->using(fn(HasOne $hasOne) => $hasOne->ofMany(value($column), $aggregate, $relation));
    }

    /**
     * @inheritDoc
     */
    public function docblock(GuideScope $scope) : array
    {
        $doc = parent::docblock($scope);

        $doc[] = sprintf("@method %s<%s> %s()", $scope->typeHint(HasOne::class), $scope->typeHint($this->related::class), $this->name);
        $doc[] = sprintf("@property ?%s \$%s", $scope->typeHint($this->related::class), $this->name);

        return $doc;
    }

}