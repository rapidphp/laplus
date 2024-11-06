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
class ModelGuide extends GuideAuthor
{

    protected function guide(string $contents)
    {
        $docblock = [];
        $uses = class_uses_recursive($this->class);
        $model = app($this->class);

        $scope = $this->makeScope($contents);

        if (in_array(HasPresent::class, $uses))
        {
            /** @var Present $present */
            $present = $model->getPresent();

            array_push($docblock, ...$present->docblock($scope));
        }

        if (in_array(HasLabels::class, $uses))
        {
            /** @var LabelTranslator $label */
            $label = $model->getLabelTranslator();

            array_push($docblock, ...$this->guideLabelTranslator($label));
        }

        array_push($docblock, ...$this->guideModelAttributes());
        array_push($docblock, ...$this->guideAttributes($scope));

        $contents = $this->commentClass($contents, $this->class, 'GuidePresent', $docblock);

        return $contents;
    }

    protected function guideLabelTranslator(LabelTranslator $label) : array
    {
        $docblock = [];

        foreach ($label->extractLabelNames() as $name)
        {
            $ref = new \ReflectionMethod($label, Str::camel($name));

            if ($info = $ref->getDocComment())
            {
                $info = preg_replace('/^\/\*\*[\s\n*]*/', '', $info);
                $info = explode("\n", $info, 2)[0];

                if (str_starts_with($info, '@'))
                {
                    $info = false;
                }
            }

            $canUseAsProperty = true;
            $methodIn = [];
            foreach ($ref->getParameters() as $parameter)
            {
                $in = '';

                if ($parameter->getType())
                {
                    $in .= (string) $parameter->getType() . ' ';
                }

                $in .= '$' . $parameter->getName();

                if ($parameter->isDefaultValueAvailable())
                {
                    $in .= ' = ' . $this->objectToDoc($parameter->getDefaultValue());
                }
                else
                {
                    $canUseAsProperty = false;
                }

                $methodIn[] = $in;
            }

            $methodIn = implode(', ', $methodIn);

            if ($canUseAsProperty)
                $docblock[] = "@property string \${$name}_label" . ($info ? ' ' . $info : '');

            $docblock[] = "@method string {$name}_label($methodIn)" . ($info ? ' ' . $info : '');
        }

        return $docblock;
    }

    protected function guideModelAttributes(GuideScope $scope) : array
    {
        $attributes = [];
        $docblock = [];

        foreach ((new \ReflectionClass($this->class))->getMethods() as $method)
        {
            if (preg_match('/^(get|set)([A-Z][a-zA-Z0-9_]*)Attribute$/', $method->name, $matches))
            {
                if (!in_array($matches[2], ['ClassCastable', 'EnumCastable']))
                {
                    $name = Str::snake($matches[2]);

                    if ($matches[1] == 'get')
                    {
                        @$attributes[$name]['get'] = (string) ($method->getReturnType() ?? 'mixed');
                    }
                    else
                    {
                        @$attributes[$name]['set'] = (string) (@$method->getParameters()[0]?->getType() ?? 'mixed');
                    }
                }
            }
        }

        foreach ($attributes as $name => $value)
        {
            $type = $scope->typeHint($value['get'] ?? $value['set']);
            $docblock[] = "@property {$type} \${$name}";
        }

        return $docblock;
    }

    protected function guideAttributes(GuideScope $scope) : array
    {
        $docblock = [];
        $class = new \ReflectionClass($this->class);

        foreach ($class->getAttributes() as $attribute)
        {
            if (is_a($attribute->getName(), DocblockAttributeContract::class, true))
            {
                array_push($docblock, ...$attribute->newInstance()->docblock($scope, $class));
            }
        }

        foreach ($class->getMethods() as $method)
        {
            foreach ($method->getAttributes() as $attribute)
            {
                if (is_a($attribute->getName(), DocblockAttributeContract::class, true))
                {
                    array_push($docblock, ...$attribute->newInstance()->docblock($scope, $method));
                }
            }
        }

        foreach ($class->getProperties() as $property)
        {
            foreach ($property->getAttributes() as $attribute)
            {
                if (is_a($attribute->getName(), DocblockAttributeContract::class, true))
                {
                    array_push($docblock, ...$attribute->newInstance()->docblock($scope, $property));
                }
            }
        }

        return $docblock;
    }

}