<?php

namespace Rapid\Laplus\Label;

use Rapid\Laplus\Present\Present;

trait HasLabels
{

    /**
     * Get label of attribute
     *
     * @param string $name
     * @param        ...$args
     * @return string
     */
    public function label(string $name, ...$args) : string
    {
        // Get using label translator
        if (($labelTranslator = $this->getLabelTranslator())?->hasLabel($name))
        {
            return $labelTranslator->getLabel($name, ...$args);
        }

        // Get using present
        /** @var Present $present */
        if (
            method_exists($this, 'getPresent') &&
            ($present = $this->getPresent()) &&
            ($attr = $present->getAttribute($name))
        )
        {
            return $attr->getLabelFor($this->getAttribute($name), $args);
        }

        throw new \InvalidArgumentException(sprintf("Label [%s] is not defined in [%s]", $name, static::class));
    }

    /**
     * Get label if attribute is ends with "_label" and is label request.
     * Used in "__get" method.
     *
     * @param string $attribute
     * @param array  $args
     * @return string|null
     */
    protected function getLabelUsingAttributeName(string $attribute, array $args = [])
    {
        if (str_ends_with($attribute, '_label'))
        {
            return $this->label(substr($attribute, 0, -6), ...$args);
        }

        return null;
    }

    public function __get($key)
    {
        if (null !== $value = $this->getLabelUsingAttributeName($key))
        {
            return $value;
        }

        return parent::__get($key);
    }

    public function __call($method, $parameters)
    {
        if (null !== $value = $this->getLabelUsingAttributeName($method, $parameters))
        {
            return $value;
        }

        return parent::__call($method, $parameters);
    }


    protected LabelTranslator|bool $_labelTranslator;

    /**
     * Get the label translator object
     *
     * @return ?LabelTranslator
     */
    protected function makeLabelTranslator() : ?LabelTranslator
    {
        if ($class = $this->getLabelTranslatorClass())
        {
            return new $class($this);
        }

        return LabelTranslator::makeLabelTranslatorFor($this);
    }

    /**
     * Get the label translator class name
     *
     * @return string|null
     */
    protected function getLabelTranslatorClass() : ?string
    {
        return null;
    }

    /**
     * Get the label translator instance (value will be cached)
     *
     * @return ?LabelTranslator
     */
    public function getLabelTranslator()
    {
        if (!isset($this->_labelTranslator))
        {
            $this->_labelTranslator = $this->makeLabelTranslator() ?? false;
        }

        return $this->_labelTranslator === false ? null : $this->_labelTranslator;
    }

}