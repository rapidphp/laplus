<?php

namespace Rapid\Laplus\Present\Traits;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Rapid\Laplus\Present\Attributes\BelongsToAttr;
use Rapid\Laplus\Present\Attributes\BelongsToManyAttr;
use Rapid\Laplus\Present\Attributes\HasManyAttr;
use Rapid\Laplus\Present\Attributes\HasManyThroughAttr;
use Rapid\Laplus\Present\Attributes\HasOneAttr;
use Rapid\Laplus\Present\Attributes\HasOneThroughAttr;
use Rapid\Laplus\Present\Attributes\MorphManyAttr;
use Rapid\Laplus\Present\Attributes\MorphOneAttr;
use Rapid\Laplus\Present\Attributes\MorphsAttr;
use Rapid\Laplus\Present\Attributes\MorphToManyAttr;

/**
 * @internal
 */
trait Relations
{

    /**
     * Create a belongsTo relationship
     *
     * @param string      $model
     * @param string|null $relation
     * @param string|null $foreignKey
     * @param string|null $ownerKey
     * @param string      $columnType
     * @return BelongsToAttr
     */
    public function belongsTo(string $model, string $relation = null, string $foreignKey = null, string $ownerKey = null, string $columnType = 'unsignedBigInteger')
    {
        $model = $model::getPresentableInstance();

        $ownerKeyR = $ownerKey ?? $model->getKeyName();
        $relationR = $relation ?? Str::snake(class_basename($model));
        $foreignKey ??= isset($relation) || isset($ownerKey) ? $relationR . '_' . $ownerKeyR : $model->getForeignKey();

        return $this->attribute(
            new BelongsToAttr($model, $foreignKey, $ownerKeyR, $relationR, $columnType)
        );
    }

    /**
     * Create a foreign key
     *
     * @param string      $model
     * @param string|null $foreignKey
     * @param string|null $ownerKey
     * @param string      $columnType
     * @return BelongsToAttr
     */
    public function foreignTo(string $model, string $foreignKey = null, string $ownerKey = null, string $columnType = 'unsignedBigInteger')
    {
        return $this->belongsTo($model, null, $foreignKey, $ownerKey, $columnType)->excludeAttr();
    }

    /**
     * Create a hasOne relationship
     *
     * @param string      $model
     * @param string|null $relation
     * @param string|null $foreignKey
     * @param string|null $localKey
     * @return HasOneAttr
     */
    public function hasOne(string $model, string $relation = null, string $foreignKey = null, string $localKey = null)
    {
        $model = $model::getPresentableInstance();

        $localKey ??= $this->instance->getKeyName();
        $foreignKey ??= $this->instance->getForeignKey();
        $relation ??= Str::camel(class_basename($model));

        return $this->attribute(new HasOneAttr($model, $foreignKey, $localKey, $relation));
    }

    /**
     * Create a hasMany relationship
     *
     * @param string      $model
     * @param string|null $relation
     * @param string|null $foreignKey
     * @param string|null $localKey
     * @return HasManyAttr
     */
    public function hasMany(string $model, string $relation = null, string $foreignKey = null, string $localKey = null)
    {
        $model = $model::getPresentableInstance();

        $localKey ??= $this->instance->getKeyName();
        $foreignKey ??= $this->instance->getForeignKey();
        $relation ??= Str::camel(Str::plural(class_basename($model)));

        return $this->attribute(new HasManyAttr($model, $foreignKey, $localKey, $relation));
    }

    /**
     * Create new belongsToMany relationship
     *
     * @param string      $model
     * @param string|null $relation
     * @param string|null $pivot
     * @param string|null $foreignPivotKey
     * @param string|null $relatedPivotKey
     * @param string|null $parentKey
     * @param string|null $relatedKey
     * @return BelongsToManyAttr
     */
    public function belongsToMany(
        string $model,
        string $relation = null,
        string $pivot = null,
        string $foreignPivotKey = null,
        string $relatedPivotKey = null,
        string $parentKey = null,
        string $relatedKey = null
    )
    {
        $model = $model::getPresentableInstance();
        if (isset($pivot)) $pivot = $pivot::getPresentableInstance();

        $relation ??= Str::camel(Str::plural(class_basename($model)));

        return $this->attribute(
            new BelongsToManyAttr($model, $pivot, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relation)
        );
    }

    /**
     * Create new hasOneThrough relationship
     *
     * @param string      $related
     * @param string      $through
     * @param string|null $relation
     * @param string|null $firstKey
     * @param string|null $secondKey
     * @param string|null $localKey
     * @param string|null $secondLocalKey
     * @return HasOneThroughAttr
     */
    public function hasOneThrough(
        string $related,
        string $through,
        string $relation = null,
        string $firstKey = null,
        string $secondKey = null,
        string $localKey = null,
        string $secondLocalKey = null
    )
    {
        $related = $related::getPresentableInstance();
        $through = $through::getPresentableInstance();

        $localKey ??= $this->instance->getKeyName();
        $secondLocalKey ??= $through->getKeyName();
        $firstKey ??= $this->instance->getForeignKey();
        $secondKey ??= $through->getForeignKey();

        $relation ??= Str::camel(class_basename($through)) . Str::camel(class_basename($related));

        return $this->attribute(
            new HasOneThroughAttr($related, $through, $firstKey, $secondKey, $localKey, $secondLocalKey, $relation)
        );
    }

    /**
     * Create new hasManyThrough relationship
     *
     * @param string      $related
     * @param string      $through
     * @param string|null $relation
     * @param string|null $firstKey
     * @param string|null $secondKey
     * @param string|null $localKey
     * @param string|null $secondLocalKey
     * @return HasManyThroughAttr
     */
    public function hasManyThrough(
        string $related,
        string $through,
        string $relation = null,
        string $firstKey = null,
        string $secondKey = null,
        string $localKey = null,
        string $secondLocalKey = null,
    )
    {
        $related = $related::getPresentableInstance();
        $through = $through::getPresentableInstance();

        $localKey ??= $this->instance->getKeyName();
        $secondLocalKey ??= $through->getKeyName();
        $firstKey ??= $this->instance->getForeignKey();
        $secondKey ??= $through->getForeignKey();

        $relation ??= Str::camel(class_basename($through)) . Str::camel(Str::plural(class_basename($related)));

        return $this->attribute(
            new HasManyThroughAttr($related, $through, $firstKey, $secondKey, $localKey, $secondLocalKey, $relation)
        );
    }

    /**
     * Define morphs columns and relationship
     *
     * @param string      $name
     * @param string|null $relation
     * @param string|null $indexName
     * @return MorphsAttr
     */
    public function morphs(string $name, string $relation = null, string $indexName = null)
    {
        $relation ??= $name;

        return $this->attribute(
            new MorphsAttr($name, $relation, $indexName)
        );
    }

    /**
     * Create new morphMany relationship
     *
     * @param string $related
     * @param string $name
     * @param string $relation
     * @return MorphManyAttr
     */
    public function morphMany(string $related, string $name, string $relation)
    {
        $related = $related::getPresentableInstance();

        return $this->attribute(
            new MorphManyAttr($related, $name, $relation)
        );
    }

    /**
     * Create new morphOne relationship
     *
     * @param string $related
     * @param string $name
     * @param string $relation
     * @return MorphOneAttr
     */
    public function morphOne(string $related, string $name, string $relation)
    {
        $related = $related::getPresentableInstance();

        return $this->attribute(
            new MorphOneAttr($related, $name, $relation)
        );
    }

    /**
     * Create new morphToMany relationship
     *
     * @param string      $related
     * @param string      $name
     * @param string      $relation
     * @param string|null $pivot
     * @param string|null $foreignPivotKey
     * @param string|null $relatedPivotKey
     * @param string|null $parentKey
     * @param string|null $relatedKey
     * @param bool        $inverse
     * @return MorphToManyAttr
     */
    public function morphToMany(
        string $related, string $name, string $relation, string $pivot = null, string $foreignPivotKey = null,
        string $relatedPivotKey = null, string $parentKey = null,
        string $relatedKey = null, bool $inverse = false
    )
    {
        $related = $related::getPresentableInstance();
        $pivot = $pivot::getPresentableInstance();

        return $this->attribute(
            new MorphToManyAttr(
                $related, $name, $relation, $pivot,
                $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $inverse,
            )
        );
    }

    /**
     * Create new morphedByMany relationship
     *
     * @param string      $related
     * @param string      $name
     * @param string      $relation
     * @param string|null $pivot
     * @param string|null $foreignPivotKey
     * @param string|null $relatedPivotKey
     * @param string|null $parentKey
     * @param string|null $relatedKey
     * @return MorphToManyAttr
     */
    public function morphedByMany(
        string $related, string $name, string $relation, string $pivot = null, string $foreignPivotKey = null,
        string $relatedPivotKey = null, string $parentKey = null,
        string $relatedKey = null
    )
    {
        $related = $related::getPresentableInstance();
        $pivot = $pivot::getPresentableInstance();

        $foreignPivotKey ??= $this->instance->getForeignKey();
        $relatedPivotKey ??= $name.'_id';

        return $this->attribute(
            new MorphToManyAttr(
                $related, $name, $relation, $pivot,
                $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, true,
            )
        );
    }
}