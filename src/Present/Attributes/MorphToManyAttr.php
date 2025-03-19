<?php

namespace Rapid\Laplus\Present\Attributes;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Rapid\Laplus\Contracts\RelationAttr;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Present\Present;

class MorphToManyAttr extends Attribute implements RelationAttr
{

    protected array $using = [];

    public function __construct(
        public Model   $related,
        public string  $morphName,
        string         $relationName,
        public Model   $pivot,
        public ?string $foreignPivotKey = null,
        public ?string $relatedPivotKey = null,
        public ?string $parentKey = null,
        public ?string $relatedKey = null,
        public bool    $inverse = false,
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
     * @inheritDoc
     */
    public function docblock(GuideScope $scope): array
    {
        $doc = parent::docblock($scope);

        $doc[] = sprintf("@method %s<%s> %s()", $scope->typeHint(MorphToMany::class), $scope->typeHint($this->related::class), $this->name);
        $doc[] = sprintf("@property-read %s<int, %s> \$%s", $scope->typeHint(Collection::class), $scope->typeHint($this->related::class), $this->name);

        return $doc;
    }

}