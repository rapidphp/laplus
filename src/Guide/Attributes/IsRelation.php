<?php

namespace Rapid\Laplus\Guide\Attributes;

use Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Rapid\Laplus\Guide\GuideScope;
use ReflectionMethod;

#[Attribute(Attribute::TARGET_METHOD)]
class IsRelation implements DocblockAttributeContract
{

    public function __construct(
        public null|string|array $type = null,
    )
    {
    }

    public function docblock(GuideScope $scope, $reflection): array
    {
        if ($reflection instanceof ReflectionMethod) {
            $sample = $reflection->invoke($reflection->getDeclaringClass()->newInstance());

            if (isset($this->type)) {
                $typeHint = implode('|', array_map($scope->typeHint(...), (array)$this->type));
            } elseif ($sample instanceof MorphTo) {
                $typeHint = $scope->typeHint(Model::class);
            } elseif (method_exists($sample, 'getRelated')) {
                $typeHint = $scope->typeHint(get_class($sample->getRelated()));
            } else {
                $typeHint = 'mixed';
            }

            $summary = $scope->summary($reflection->getDocComment());

            if ($sample instanceof BelongsTo || $sample instanceof HasOne || $sample instanceof HasOneThrough ||
                $sample instanceof MorphOne) {
                return [
                    sprintf("@property-read null|%s $%s", $typeHint, $reflection->getName()) . ($summary ? ' ' . $summary : ''),
                ];
            }

            if ($sample instanceof HasMany || $sample instanceof BelongsToMany || $sample instanceof HasManyThrough ||
                $sample instanceof MorphMany) {
                return [
                    sprintf("@property-read %s<int, %s> $%s", $scope->typeHint(Collection::class), $typeHint, $reflection->getName()) . ($summary ? ' ' . $summary : ''),
                ];
            }

            throw new \TypeError(sprintf(
                "Method %s::%s() is not a relationship",
                $reflection->getDeclaringClass()->getName(),
                $reflection->getName(),
            ));
        }

        return [];
    }

}