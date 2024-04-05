<?php

namespace Rapid\Laplus\Present\Traits;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Rapid\Laplus\Present\Attributes\BelongsTo;
use Rapid\Laplus\Present\Attributes\BelongsToMany;
use Rapid\Laplus\Present\Attributes\HasMany;
use Rapid\Laplus\Present\Attributes\HasOne;

trait HasRelations
{

    public function foreignColumn(string $column, string $model)
    {
        
    }

    /**
     * Create a belongsTo relationship
     *
     * @param string      $model
     * @param string|null $relation
     * @param string|null $foreignKey
     * @param string|null $ownerKey
     * @param string      $columnType
     * @return BelongsTo
     */
    public function belongsTo(string $model, string $relation = null, string $foreignKey = null, string $ownerKey = null, string $columnType = 'unsignedBigInteger')
    {
        $model = $model::getPresentInstance();

        $ownerKeyR = $ownerKey ?? $model->getKeyName();
        $relationR = $relation ?? Str::snake(class_basename($model));
        $foreignKey ??= isset($relation) || isset($ownerKey) ? $relationR . '_' . $ownerKeyR : $model->getForeignKey();

        return $this->attribute(
            new BelongsTo($model, $foreignKey, $ownerKeyR, $relationR, $columnType)
        );
    }

    /**
     * Create a hasOne relationship
     *
     * @param string      $model
     * @param string|null $relation
     * @param string|null $foreignKey
     * @param string|null $localKey
     * @return HasOne
     */
    public function hasOne(string $model, string $relation = null, string $foreignKey = null, string $localKey = null)
    {
        $model = $model::getPresentInstance();

        $localKey ??= $this->instance->getKeyName();
        $foreignKey ??= $this->instance->getForeignKey();
        $relation ??= Str::camel(class_basename($model));

        return $this->attribute(new HasOne($model, $foreignKey, $localKey, $relation));
    }

    /**
     * Create a hasMany relationship
     *
     * @param string      $model
     * @param string|null $relation
     * @param string|null $foreignKey
     * @param string|null $localKey
     * @return HasMany
     */
    public function hasMany(string $model, string $relation = null, string $foreignKey = null, string $localKey = null)
    {
        $model = $model::getPresentInstance();

        $localKey ??= $this->instance->getKeyName();
        $foreignKey ??= $this->instance->getForeignKey();
        $relation ??= Str::camel(Str::plural(class_basename($model)));

        return $this->attribute(new HasMany($model, $foreignKey, $localKey, $relation));
    }

    /**
     * Create new belongsToMany relationship
     *
     * @param string      $model
     * @param string|null $relation
     * @param string|null $table
     * @param string|null $foreignPivotKey
     * @param string|null $relatedPivotKey
     * @param string|null $parentKey
     * @param string|null $relatedKey
     * @return BelongsToMany
     */
    public function belongsToMany(
        string $model,
        string $relation = null,
        string $table = null,
        string $foreignPivotKey = null,
        string $relatedPivotKey = null,
        string $parentKey = null,
        string $relatedKey = null
    )
    {
        $model = $model::getPresentInstance();

        $relation ??= Str::camel(Str::plural(class_basename($model)));

        return $this->attribute(
            new BelongsToMany($model, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relation)
        );
    }

}