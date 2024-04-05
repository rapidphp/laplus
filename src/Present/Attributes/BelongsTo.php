<?php

namespace Rapid\Laplus\Present\Attributes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Rapid\Laplus\Present\Present;

class BelongsTo extends Attribute
{
    public function __construct(
        public Model $related,
        string $foreignKey,
        public string $ownerKey,
        public string $relationName,
        public string $columnType,
    )
    {
        parent::__construct($foreignKey);
        $this->fillable = true;
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

        $present->instance::resolveRelationUsing($this->relationName, $this->getRelation(...));
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

        /** @var ColumnDefinition $column */
        $table->{$this->columnType}($this->name)
            ->nullable($this->nullable);

        $table->foreign($this->name)
            ->references($this->ownerKey)
            ->on($this->related->getTable())
            ->onDelete($this->onDelete);
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
            $model
        );
    }



    protected bool $nullable = false;

    public function nullable(bool $value = true)
    {
        $this->nullable = $value;
        return $this;
    }


    protected $withDefault = false;

    public function withDefault(array|Closure|bool $callback = true)
    {
        $this->withDefault = $callback;
        return $this;
    }


    protected string $onDelete = 'restrict';

    public function cascadeOnDelete()
    {
        $this->onDelete = 'cascade';

        return $this;
    }

    public function nullOnDelete()
    {
        $this->nullable();
        $this->onDelete = 'set null';

        return $this;
    }

    public function noActionOnDelete()
    {
        $this->onDelete = 'no action';

        return $this;
    }

    public function restrictOnDelete()
    {
        $this->onDelete = 'restrict';

        return $this;
    }


    protected array $using = [];

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