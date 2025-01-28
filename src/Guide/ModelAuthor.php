<?php

namespace Rapid\Laplus\Guide;

use Illuminate\Support\Str;
use Rapid\Laplus\Guide\Attributes\DocblockAttributeContract;
use Rapid\Laplus\Label\HasLabels;
use Rapid\Laplus\Label\LabelTranslator;
use Rapid\Laplus\Present\HasPresent;
use Rapid\Laplus\Present\Present;

/**
 * @internal
 */
class ModelAuthor extends GuideAuthor
{

    public function docblock(GuideScope $scope): array
    {
        $docblock = [];
        $uses = class_uses_recursive($this->class);
        $model = app($this->class);

        if (in_array(HasPresent::class, $uses)) {
            /** @var Present $present */
            $present = $model->getPresent();

            array_push($docblock, ...$present->docblock($scope));
        }

        if (in_array(HasLabels::class, $uses)) {
            /** @var LabelTranslator $label */
            $label = $model->getLabelTranslator();

            array_push($docblock, ...$this->guideLabelTranslator($scope, $label));
        }

        array_push($docblock, ...$this->guideModelAttributes($scope));
        array_push($docblock, ...$this->guideAttributes($scope));

        return $docblock;
    }

    protected function guideLabelTranslator(GuideScope $scope, LabelTranslator $label): array
    {
        $docblock = [];

        foreach ($label->extractLabelNames() as $name) {
            $ref = new \ReflectionMethod($label, Str::camel($name));

            $info = $scope->summary($ref->getDocComment());

            $canUseAsProperty = true;
            $methodIn = [];
            foreach ($ref->getParameters() as $parameter) {
                $in = '';

                if ($parameter->getType()) {
                    $in .= (string)$parameter->getType() . ' ';
                }

                $in .= '$' . $parameter->getName();

                if ($parameter->isDefaultValueAvailable()) {
                    $in .= ' = ' . Document::object($parameter->getDefaultValue());
                } else {
                    $canUseAsProperty = false;
                }

                $methodIn[] = $in;
            }

            $methodIn = implode(', ', $methodIn);

            if ($canUseAsProperty)
                $docblock[] = "@property-read string \${$name}_label" . ($info ? ' ' . $info : '');

            $docblock[] = "@method string {$name}_label($methodIn)" . ($info ? ' ' . $info : '');
        }

        return $docblock;
    }

    protected function guideModelAttributes(GuideScope $scope): array
    {
        $attributes = [];
        $docblock = [];

        foreach ((new \ReflectionClass($this->class))->getMethods() as $method) {
            if (preg_match('/^(get|set)([A-Z][a-zA-Z0-9_]*)Attribute$/', $method->name, $matches)) {
                if (!in_array($matches[2], ['ClassCastable', 'EnumCastable'])) {
                    $name = Str::snake($matches[2]);

                    if ($matches[1] == 'get') {
                        @$attributes[$name]['get'] = (string)($method->getReturnType() ?? 'mixed');
                        @$attributes[$name]['summary'] = $scope->summary($method->getDocComment());
                    } else {
                        @$attributes[$name]['set'] = (string)(@$method->getParameters()[0]?->getType() ?? 'mixed');
                        @$attributes[$name]['summary'] ??= $scope->summary($method->getDocComment());
                    }
                }
            }
        }

        foreach ($attributes as $name => $value) {
            $type = implode('|', array_unique([
                ...isset($value['get']) ? explode('|', $scope->typeHint($value['get'])) : [],
                ...isset($value['set']) ? explode('|', $scope->typeHint($value['set'])) : [],
            ]));

            $accessSuffix = match (true) {
                isset($value['get']) && !isset($value['set']) => '-read',
                isset($value['set']) && !isset($value['get']) => '-write',
                default                                       => null,
            };

            $camelCase = Str::camel($name);

            $summary = $value['summary'];
            $docblock[] = "@property{$accessSuffix} {$type} \${$name}" . ($summary ? ' ' . $summary : '');
            if ($name != $camelCase) {
                $docblock[] = "@property{$accessSuffix} {$type} \${$camelCase}" . ($summary ? ' ' . $summary : '');
            }
        }

        return $docblock;
    }

    protected function guideAttributes(GuideScope $scope): array
    {
        $docblock = [];
        $class = new \ReflectionClass($this->class);

        foreach ($class->getAttributes() as $attribute) {
            if (is_a($attribute->getName(), DocblockAttributeContract::class, true)) {
                array_push($docblock, ...$attribute->newInstance()->docblock($scope, $class));
            }
        }

        foreach ($class->getMethods() as $method) {
            foreach ($method->getAttributes() as $attribute) {
                if (is_a($attribute->getName(), DocblockAttributeContract::class, true)) {
                    array_push($docblock, ...$attribute->newInstance()->docblock($scope, $method));
                }
            }
        }

        foreach ($class->getProperties() as $property) {
            foreach ($property->getAttributes() as $attribute) {
                if (is_a($attribute->getName(), DocblockAttributeContract::class, true)) {
                    array_push($docblock, ...$attribute->newInstance()->docblock($scope, $property));
                }
            }
        }

        return $docblock;
    }

}