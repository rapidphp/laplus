<?php

namespace Rapid\Laplus\Guide\Attributes;

use Attribute;
use Illuminate\Database\Eloquent\Casts\Attribute as LaravelAttribute;
use Illuminate\Support\Str;
use Rapid\Laplus\Guide\GuideScope;

#[Attribute(Attribute::TARGET_METHOD)]
class IsAttribute implements DocblockAttributeContract
{
    public function __construct(
        public string|array $type = 'mixed',
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

        $accessSuffix = match (true) {
            isset($attribute->get) && !isset($attribute->set) => '-read',
            isset($attribute->set) && !isset($attribute->get) => '-write',
            default => null,
        };

        $typeHint = implode('|', array_map($scope->typeHint(...), (array)$this->type));
        $summary = $scope->summary($reflection->getDocComment());

        return array_unique([
            sprintf("@property%s %s \$%s", $accessSuffix, $typeHint, $reflection->name) . ($summary ? ' ' . $summary : ''),
            sprintf("@property%s %s \$%s", $accessSuffix, $typeHint, Str::snake($reflection->name)) . ($summary ? ' ' . $summary : ''),
        ]);
    }
}