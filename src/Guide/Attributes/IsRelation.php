<?php

namespace Rapid\Laplus\Guide\Attributes;

use Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Rapid\Laplus\Guide\GuideScope;
use ReflectionMethod;

#[Attribute(Attribute::TARGET_METHOD)]
class IsRelation implements DocblockAttributeContract
{

    public function docblock(GuideScope $scope, $reflection) : array
    {
        if ($reflection instanceof ReflectionMethod)
        {
            $sample = $reflection->invoke($reflection->getDeclaringClass()->newInstance());

            if (
                $sample instanceof BelongsTo || $sample instanceof HasOne || $sample instanceof HasOneThrough ||
                $sample instanceof MorphOne
            )
            {
                return [
                    sprintf("@property ?%s $%s", $scope->typeHint(get_class($sample->getRelated())), $reflection->getName()),
                ];
            }

            if (
                $sample instanceof HasMany || $sample instanceof BelongsToMany || $sample instanceof HasManyThrough ||
                $sample instanceof MorphMany
            )
            {
                return [
                    sprintf("@property %s<%s> $%s", $scope->typeHint(Collection::class), $scope->typeHint(get_class($sample->getRelated())), $reflection->getName()),
                ];
            }
        }

        return [];
    }

}