<?php

namespace Rapid\Laplus\Present\Attributes;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rapid\Laplus\Contracts\RelationAttr;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Present\Present;

class MorphManyAttr extends Attribute implements RelationAttr
{

    protected array $using = [];

    public function __construct(
        public Model  $related,
        public string $morphName,
        string        $relationName,
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
     * @return MorphMany
     */
    public function getRelation(Model $model)
    {
        return $this->fireUsing(
            $model->morphMany($this->related::class, $this->morphName),
            $model,
        );
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
        foreach ($this->using as $callback) {
            $arg = $callback($arg, $model);
        }

        return $arg;
    }

    /**
     * Fire callback when creating relation
     *
     * `fn (MorphMany $relation) => $relation`
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
     * @inheritDoc
     */
    public function docblock(GuideScope $scope): array
    {
        $doc = parent::docblock($scope);

        $doc[] = sprintf("@method %s<%s> %s()", $scope->typeHint(MorphMany::class), $scope->typeHint($this->related::class), $this->name);
        $doc[] = sprintf("@property-read %s<int, %s> \$%s", $scope->typeHint(Collection::class), $scope->typeHint($this->related::class), $this->name);

        return $doc;
    }

}