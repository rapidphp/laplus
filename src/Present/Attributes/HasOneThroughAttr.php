<?php

namespace Rapid\Laplus\Present\Attributes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Rapid\Laplus\Contracts\RelationAttr;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Present\Present;

class HasOneThroughAttr extends Attribute implements RelationAttr
{

    protected $withDefault = false;
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function getRelation(Model $model)
    {
        return $this->fireUsing(
            $model->hasOneThrough($this->related::class, $this->through::class, $this->firstKey, $this->secondKey, $this->localKey, $this->secondLocalKey)
                ->withDefault($this->withDefault),
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

    /**
     * Fire callback when creating relation
     *
     * `fn (HasOneThrough $relation) => $relation`
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

        $doc[] = sprintf("@method %s<%s> %s()", $scope->typeHint(HasOneThrough::class), $scope->typeHint($this->related::class), $this->name);
        $doc[] = sprintf("@property-read ?%s \$%s", $scope->typeHint($this->related::class), $this->name);

        return $doc;
    }

}