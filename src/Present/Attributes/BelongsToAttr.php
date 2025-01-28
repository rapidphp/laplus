<?php

namespace Rapid\Laplus\Present\Attributes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Present\Present;

class BelongsToAttr extends Column
{
    protected $withDefault = false;
    protected ?string $onDelete = null;
    protected ?string $onUpdate = null;
    protected array $using = [];
    protected $includeAttr = true;

    public function __construct(
        public Model  $related,
        string        $foreignKey,
        public string $ownerKey,
        public string $relationName,
        string        $columnType,
    )
    {
        parent::__construct($foreignKey, $columnType, [$foreignKey]);
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

        if ($this->includeAttr) {
            $present->instance::resolveRelationUsing(
                $this->relationName,
                $this->getRelation(...),
            );
        }
    }

    /**
     * Generate table structure
     *
     * @param Present $present
     * @return void
     */
    public function generate(Present $present)
    {
        parent::generate($present);

        $table = $present->getGeneratingBlueprint();

        $foreign = $table->foreign($this->name)
            ->references($this->ownerKey)
            ->on($this->related->getTable())
            ->onDelete($this->onDelete ?? 'restrict');

        if ($this->onUpdate)
            $foreign->onUpdate($this->onUpdate);
    }

    /**
     * Get relation value
     *
     * @param Model $model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getRelation(Model $model)
    {
        return $this->fireUsing(
            $model->belongsTo($this->related::class, $this->name, $this->ownerKey, $this->relationName)
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
     * Cascade on delete
     *
     * @return $this
     */
    public function cascadeOnDelete()
    {
        $this->onDelete = 'cascade';

        return $this;
    }

    /**
     * Set null on delete
     *
     * @return $this
     */
    public function nullOnDelete()
    {
        $this->nullable();
        $this->onDelete = 'set null';

        return $this;
    }

    /**
     * No action on delete
     *
     * @return $this
     */
    public function noActionOnDelete()
    {
        $this->onDelete = 'no action';

        return $this;
    }

    /**
     * Restrict on delete
     *
     * @return $this
     */
    public function restrictOnDelete()
    {
        $this->onDelete = 'restrict';

        return $this;
    }

    /**
     * Cascade on delete
     *
     * @return $this
     */
    public function cascadeOnUpdate()
    {
        $this->onUpdate = 'cascade';

        return $this;
    }

    /**
     * Set null on delete
     *
     * @return $this
     */
    public function nullOnUpdate()
    {
        $this->nullable();
        $this->onUpdate = 'set null';

        return $this;
    }

    /**
     * No action on delete
     *
     * @return $this
     */
    public function noActionOnUpdate()
    {
        $this->onUpdate = 'no action';

        return $this;
    }

    /**
     * Restrict on delete
     *
     * @return $this
     */
    public function restrictOnUpdate()
    {
        $this->onUpdate = 'restrict';

        return $this;
    }

    /**
     * Fire callback when creating relation
     *
     * `fn (BelongsTo $relation) => $relation`
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
     * @inheritDoc
     */
    public function docblock(GuideScope $scope): array
    {
        $doc = parent::docblock($scope);

        if ($this->includeAttr) {
            $doc[] = sprintf("@method %s<%s> %s()", $scope->typeHint(BelongsTo::class), $scope->typeHint($this->related::class), $this->relationName);
            $doc[] = sprintf("@property-read ?%s \$%s", $scope->typeHint($this->related::class), $this->relationName);
        }

        return $doc;
    }

}