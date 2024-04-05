<?php

namespace Rapid\Laplus\Present;

use Illuminate\Database\Eloquent\Model;

trait HasPresent
{

    protected Present $presentObject;

    protected function getPresent() : Present
    {
        if ($class = $this->getPresentClass())
        {
            return Present::getPresentOfType($this, $class);
        }

        return Present::getPresentOfModel($this);
    }

    protected function getPresentClass() : ?string
    {
        return null;
    }

    public function getPresentObject()
    {
        if (!isset($this->presentObject))
        {
            static::$present_instances[static::class] = $this;
            $this->presentObject = $this->getPresent();
        }

        return $this->presentObject;
    }

    protected static $present_instances = [];

    public static function getPresentInstance() : Model
    {
        return static::$present_instances[static::class] ??= new static;
    }


    public function initializeHasPresent()
    {
        $present = $this->getPresentObject();

        $this->mergeFillable($present->fillable);
        $this->mergeCasts($present->casts);
    }


    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        if (array_key_exists($key, $this->presentObject->getters))
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
        if (array_key_exists($key, $this->presentObject->setters))
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
        if (array_key_exists($key, $this->presentObject->getters))
        {
            return $this->presentObject->getters[$key]($value, $this, $key, $this->attributes);
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
        if (array_key_exists($key, $this->presentObject->setters))
        {
            return $this->presentObject->setters[$key]($value, $this, $key, $this->attributes);
        }

        return parent::setMutatedAttributeValue($key, $value);
    }



}