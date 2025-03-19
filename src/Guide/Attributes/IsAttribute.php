<?php

namespace Rapid\Laplus\Guide\Attributes;

use Attribute;
use Closure;
use Illuminate\Database\Eloquent\Casts\Attribute as LaravelAttribute;
use Illuminate\Support\Str;
use Laravel\SerializableClosure\Support\ReflectionClosure;
use Rapid\Laplus\Guide\Document;
use Rapid\Laplus\Guide\GuideScope;
use ReflectionFunction;

#[Attribute(Attribute::TARGET_METHOD)]
class IsAttribute implements DocblockAttributeContract
{
    public function __construct(
        public string|array|null $getType = null,
        public string|array|null $setType = null,
    )
    {
    }

    public function docblock(GuideScope $scope, $reflection): array
    {
        /** @var \ReflectionMethod $reflection */
        $attribute = $reflection->invoke($reflection->getDeclaringClass()->newInstanceWithoutConstructor());

        if (!($attribute instanceof LaravelAttribute)) {
            throw new \TypeError(sprintf("Method [%s] is not an attribute on [%s]", $reflection->name, $reflection->getDeclaringClass()->name));
        }

        $getType = $setType = 'mixed';

        if (isset($this->getType)) {
            $getType = implode('|', array_map($scope->typeHint(...), (array)$this->getType));
        } elseif (isset($attribute->get)) {
            if ($returnType = (new ReflectionClosure(Closure::fromCallable($attribute->get)))->getReturnType()) {
                $getType = Document::reflectionType($scope, $returnType);
            }
        }

        if (isset($this->setType)) {
            $setType = implode('|', array_map($scope->typeHint(...), (array)$this->setType));
        } elseif (isset($attribute->get)) {
            if ($paramType = @(new ReflectionClosure(Closure::fromCallable($attribute->set)))->getParameters()[0]?->getType()) {
                $setType = Document::reflectionType($scope, $paramType);
            }
        }

        $summary = $scope->summary($reflection->getDocComment());
        $summary = $summary ? ' ' . $summary : '';

        if (isset($attribute->set) && isset($attribute->get)) {
            if ($getType == $setType) {
                return array_unique([
                    sprintf("@property %s \$%s%s", $setType, $reflection->name, $summary),
                    sprintf("@property %s \$%s%s", $setType, Str::snake($reflection->name), $summary),
                ]);
            }

            return array_unique([
                sprintf("@property-read %s \$%s%s", $getType, $reflection->name, $summary),
                sprintf("@property-read %s \$%s%s", $getType, Str::snake($reflection->name), $summary),
                sprintf("@property-write %s \$%s%s", $setType, $reflection->name, $summary),
                sprintf("@property-write %s \$%s%s", $setType, Str::snake($reflection->name), $summary),
            ]);
        }

        if (isset($attribute->set)) {
            return array_unique([
                sprintf("@property-write %s \$%s%s", $setType, $reflection->name, $summary),
                sprintf("@property-write %s \$%s%s", $setType, Str::snake($reflection->name), $summary),
            ]);
        } else {
            return array_unique([
                sprintf("@property-read %s \$%s%s", $getType, $reflection->name, $summary),
                sprintf("@property-read %s \$%s%s", $getType, Str::snake($reflection->name), $summary),
            ]);
        }
    }
}