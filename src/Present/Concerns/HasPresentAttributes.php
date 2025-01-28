<?php

namespace Rapid\Laplus\Present\Concerns;

trait HasPresentAttributes
{

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param string $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        if (($present = $this->getPresent()) && array_key_exists($key, $present->getters)) {
            return true;
        }

        return parent::hasGetMutator($key);
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param string $key
     * @return bool
     */
    public function hasSetMutator($key)
    {
        if (($present = $this->getPresent()) && array_key_exists($key, $present->setters)) {
            return true;
        }

        return parent::hasSetMutator($key);
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        if (($present = $this->getPresent()) && array_key_exists($key, $present->getters)) {
            return $present->getters[$key]($value, $this, $key, $this->attributes);
        }

        return parent::mutateAttribute($key, $value);
    }

    /**
     * Set the value of an attribute using its mutator.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function setMutatedAttributeValue($key, $value)
    {
        if (($present = $this->getPresent()) && array_key_exists($key, $present->setters)) {
            return $present->setters[$key]($value, $this, $key, $this->attributes);
        }

        return parent::setMutatedAttributeValue($key, $value);
    }

}