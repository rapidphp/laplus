<?php

namespace Rapid\Laplus\Present\Attributes;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Rapid\Laplus\Present\Present;

class BelongsToAttr extends Column
{
    public function __construct(
        public Model $related,
        string $foreignKey,
        public string $ownerKey,
        public string $relationName,
        string $columnType,
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

        if ($this->includeAttr)
        {
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

        $table->foreign($this->name)
            ->references($this->ownerKey)
            ->on($this->related->getTable())
            ->onDelete($this->onDelete ?? 'restrict');
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


    protected ?string $onDelete = null;

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

    
    protected $includeAttr = true;

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

}