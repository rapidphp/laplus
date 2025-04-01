<?php

namespace Rapid\Laplus\Present\Attributes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Rapid\Laplus\Contracts\RelationAttr;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Present\Present;

class MorphsAttr extends Attribute implements RelationAttr
{

    protected string $type = '';
    protected $nullable = false;
    protected $includeAttr = true;
    protected array $using = [];
    protected array $types;

    public function __construct(
        string         $name,
        public string  $relation,
        public ?string $indexName,
    )
    {
        parent::__construct($name);
        $this->fillable = true;
    }

    /**
     * Boots relation
     *
     * @param Present $present
     * @return void
     */
    public function boot(Present $present)
    {
        parent::boot($present);

        if ($this->isFillable()) {
            array_push($present->fillable, "{$this->name}_id", "{$this->name}_type");
        }

        if ($this->isHidden()) {
            array_push($present->hidden, "{$this->name}_id", "{$this->name}_type");
        }

        if ($this->includeAttr) {
            $present->instance::resolveRelationUsing($this->relation, $this->getRelation(...));
        }
    }

    /**
     * @param Present $present
     * @return void
     */
    public function generate(Present $present)
    {
        parent::generate($present);

        $table = $present->getGeneratingBlueprint();

        $createMethod = ($this->nullable ? 'nullable' : '') . $this->type . 'morphs';
        $table->$createMethod($this->name, $this->indexName);
    }

    /**
     * Get relation value
     *
     * @param Model $model
     * @return MorphTo
     */
    public function getRelation(Model $model)
    {
        return $this->fireUsing(
            $model->morphTo($this->relation, "{$this->name}_type", "{$this->name}_id"),
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
     * Set morphs type to uuid
     *
     * @return $this
     */
    public function uuid()
    {
        $this->type = 'uuid';
        return $this;
    }

    /**
     * Set morphs type to numeric
     *
     * @return $this
     */
    public function numeric()
    {
        $this->type = 'numeric';
        return $this;
    }

    /**
     * Set morphs type to ulid
     *
     * @return $this
     */
    public function ulid()
    {
        $this->type = 'ulid';
        return $this;
    }

    /**
     * Set morphs columns nullable
     *
     * @param bool $value
     * @return $this
     */
    public function nullable(bool $value = true)
    {
        $this->nullable = $value;
        return $this;
    }

    /**
     * Include creating relation attribute in the model
     *
     * @param bool $value
     * @return $this
     */
    public function includeAttr(bool $value = true)
    {
        $this->includeAttr = $value;
        return $this;
    }

    /**
     * Exclude creating relation attribute in the model (only foreign key)
     *
     * @param bool $value
     * @return $this
     */
    public function excludeAttr(bool $value = true)
    {
        $this->includeAttr = !$value;
        return $this;
    }

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

    public function types(string|array $classes)
    {
        $this->types = (array)$classes;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function docblock(GuideScope $scope): array
    {
        $doc = parent::docblock($scope);

        $instances = $this->types ?? [Model::class];

        if ($this->includeAttr) {
            $doc[] = sprintf("@method %s %s()", $scope->typeHint(MorphTo::class), $this->name);
            $doc[] = sprintf("@property-read null|%s \$%s", implode('|', array_map($scope->typeHint(...), $instances)), $this->name);
            $doc[] = sprintf("@property string \$%s_type", $this->name);
            $doc[] = sprintf("@property int|string \$%s_id", $this->name);
        }

        return $doc;
    }

}