<?php

namespace Rapid\Laplus\Guide;

use Illuminate\Support\Str;
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
}