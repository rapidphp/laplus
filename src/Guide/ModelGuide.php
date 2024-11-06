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

        if (in_array(HasPresent::class, $uses))
        {
            /** @var Present $present */
            $present = $model->getPresent();

            array_push($docblock, ...$present->docblock());
        }

        if (in_array(HasLabels::class, $uses))
        {
            /** @var LabelTranslator $label */
            $label = $model->getLabelTranslator();

            array_push($docblock, ...$this->guideLabelTranslator($label));
        }

        array_push($docblock, ...$this->guideModelAttributes());
        array_push($docblock, ...$this->guideAttributes());

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

    protected function guideModelAttributes() : array
    {
        $pass = [];
        $docblock = [];

        foreach ((new \ReflectionClass($this->class))->getMethods() as $method)
        {
            if (preg_match('/^(get|set)([A-Z][a-zA-Z0-9_]*)Attribute$/', $method->name, $matches))
            {
                if (!in_array($matches[2], $pass) && !in_array($matches[2], ['ClassCastable', 'EnumCastable']))
                {
                    $pass[] = $matches[2];
                    $docblock[] = "@property " . ($method->getReturnType() ?? 'mixed') . " \${$matches[2]}";
                }
            }
        }

        return $docblock;
    }

    protected function guideAttributes() : array
    {
        $docblock = [];
        $class = new \ReflectionClass($this->class);

        foreach ($class->getAttributes() as $attribute)
        {
            if (is_a($attribute->getName(), DocblockAttributeContract::class))
            {
                array_push($docblock, ...$attribute->newInstance()->guide($class));
            }
        }

        foreach ($class->getMethods() as $method)
        {
            foreach ($method->getAttributes() as $attribute)
            {
                if (is_a($attribute->getName(), DocblockAttributeContract::class))
                {
                    array_push($docblock, ...$attribute->newInstance()->guide($method));
                }
            }
        }

        foreach ($class->getProperties() as $property)
        {
            foreach ($property->getAttributes() as $attribute)
            {
                if (is_a($attribute->getName(), DocblockAttributeContract::class))
                {
                    array_push($docblock, ...$attribute->newInstance()->guide($property));
                }
            }
        }

        return $docblock;
    }

}