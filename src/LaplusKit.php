<?php

namespace Rapid\Laplus;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Migrations\Migration;
use Rapid\Laplus\Discover\DiscoveredRelation;
use Rapid\Laplus\Present\Attributes\BelongsToAttr;
use Rapid\Laplus\Present\Attributes\MorphsAttr;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Resources\Resource;
use Rapid\Laplus\Resources\ResourceObject;
use Rapid\Laplus\Travel\Travel;

final class LaplusKit
{
    /**
     * Discover all the models that using by Laplus
     *
     * @return class-string<Model>[]
     */
    public static function discoverModels(): array
    {
        return collect(Laplus::getResources())
            ->map(fn (Resource $resource) => $resource->resolve())
            ->flatten()
            ->map(fn (ResourceObject $object) => $object->discoverModels())
            ->flatten()
            ->all();
    }

    /**
     * Discover all the travels that using by Laplus
     *
     * @return Travel[]
     */
    public static function discoverTravels(): array
    {
        return collect(Laplus::getResources())
            ->map(fn (Resource $resource) => $resource->resolve())
            ->flatten()
            ->map(fn (ResourceObject $object) => $object->discoverTravels())
            ->flatten()
            ->all();
    }

    /**
     * Discover all the migrations that using by Laplus
     *
     * @return Migration[]
     */
    public static function discoverMigrations(): array
    {
        return collect(Laplus::getResources())
            ->map(fn (Resource $resource) => $resource->resolve())
            ->flatten()
            ->map(fn (ResourceObject $object) => $object->discoverMigrations())
            ->flatten()
            ->all();
    }

    /**
     * Discover all the belongsTo relations that pointed at the model
     *
     * @return DiscoveredRelation[]
     */
    public static function discoverBelongsToRelations(?string $toModel = null): array
    {
        $discovered = [];

        foreach (self::discoverModels() as $model) {
            /** @var Present $present */
            $present = $model::getStaticPresentInstance();

            foreach ($present->getAttributes() as $attribute) {
                if ($attribute instanceof BelongsToAttr) {
                    if (isset($toModel) && get_class($attribute->related) !== $toModel) {
                        continue;
                    }

                    $discovered[] = new DiscoveredRelation($model, $attribute->relationName);
                }
            }
        }

        return $discovered;
    }

    /**
     * @return array
     */
    public static function discoverMorphsToRelations(): array
    {
        $discovered = [];

        foreach (self::discoverModels() as $model) {
            /** @var Present $present */
            $present = $model::getStaticPresentInstance();

            foreach ($present->getAttributes() as $attribute) {
                if ($attribute instanceof MorphsAttr) {
                    $discovered[] = new DiscoveredRelation($model, $attribute->relation);
                }
            }
        }

        return $discovered;
    }

    /**
     * Detect not attached records and return ids
     *
     * @param class-string<Model> $model
     * @return array<string|int>
     */
    public static function detectNotAttachedRecords(string $model): array
    {
        $attachedIds = [];

        foreach (self::discoverBelongsToRelations($model) as $relation) {
            $morphTo = (new ($relation->model))->getRelation($relation->relation);

            if ($morphTo instanceof BelongsTo) {
                $key = $morphTo->getForeignKeyName();
                $attachedIds = array_merge(
                    $attachedIds,
                    $relation->model::query()
                        ->distinct()
                        ->whereNotNull($key)
                        ->pluck($key)
                        ->all()
                );
            }
        }

        $morphClass = (new $model)->getMorphClass();

        foreach (self::discoverMorphsToRelations() as $relation) {
            $morphTo = (new ($relation->model))->getRelation($relation->relation);

            if ($morphTo instanceof MorphTo) {
                $attachedIds = array_merge(
                    $attachedIds,
                    $relation->model::query()
                        ->distinct()
                        ->where($morphTo->getMorphType(), $morphClass)
                        ->pluck($morphTo->getForeignKeyName())
                        ->all()
                );
            }
        }

        $modelKey = (new $model)->getKeyName();

        if (empty($attachedIds)) {
            return $model::query()->pluck($modelKey)->all();
        }

        return $model::query()
            ->whereNotIn($modelKey, array_unique($attachedIds))
            ->pluck($modelKey)
            ->all();
    }
}