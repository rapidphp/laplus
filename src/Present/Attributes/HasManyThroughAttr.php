<?php

namespace Rapid\Laplus\Present\Attributes;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Rapid\Laplus\Contracts\RelationAttr;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Present\Present;

class HasManyThroughAttr extends Attribute implements RelationAttr
{

    protected array $using = [];

    public function __construct(
        public Model  $related,
        public Model  $through,
        public string $firstKey,
        public string $secondKey,
        public string $localKey,
        public string $secondLocalKey,
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
     * @return HasManyThrough
     */
    public function getRelation(Model $model)
    {
        return $this->fireUsing(
            $model->hasManyThrough($this->related::class, $this->through::class, $this->firstKey, $this->secondKey, $this->localKey, $this->secondLocalKey),
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
     * `fn (HasManyThrough $relation) => $relation`
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

        $doc[] = sprintf("@method %s<%s> %s()", $scope->typeHint(HasManyThrough::class), $scope->typeHint($this->related::class), $this->name);
        $doc[] = sprintf("@property-read %s<int, %s> \$%s", $scope->typeHint(Collection::class), $scope->typeHint($this->related::class), $this->name);

        return $doc;
    }

}