<?php

namespace Rapid\Laplus\Present\Concerns;

trait HasPresentAttributes
{

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        if (array_key_exists($key, $this->_presentObject->getters))
        {
            return true;
        }

        return parent::hasGetMutator($key);
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasSetMutator($key)
    {
        if (array_key_exists($key, $this->_presentObject->setters))
        {
            return true;
        }

        return parent::hasSetMutator($key);
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        if (array_key_exists($key, $this->_presentObject->getters))
        {
            return $this->_presentObject->getters[$key]($value, $this, $key, $this->attributes);
        }

        return parent::mutateAttribute($key, $value);
    }

    /**
     * Set the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function setMutatedAttributeValue($key, $value)
    {
        if (array_key_exists($key, $this->_presentObject->setters))
        {
            return $this->_presentObject->setters[$key]($value, $this, $key, $this->attributes);
        }

        return parent::setMutatedAttributeValue($key, $value);
    }

}