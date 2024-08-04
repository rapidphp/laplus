<?php

namespace Rapid\Laplus\Present;

use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\Attributes\Attribute;
use Rapid\Laplus\Present\Attributes\FileColumn;

trait HasPresent
{

    protected Present $_presentObject;

    // /**
    //  * Present the model inline
    //  *
    //  * @return void
    //  */
    // public function present(Present $present)
    // {
    // }

    /**
     * Get the present object
     *
     * @return Present
     */
    protected function makePresent() : Present
    {
        if (method_exists($this, 'present'))
        {
            return Present::inline($this, $this->present(...));
        }

        if ($class = $this->getPresentClass())
        {
            return Present::getPresentOfType($this, $class);
        }

        return Present::getPresentOfModel($this);
    }

    /**
     * Get the present class name
     *
     * @return string|null
     */
    protected function getPresentClass() : ?string
    {
        return null;
    }

    /**
     * Get the present instance (value will be cached)
     *
     * @return Present
     */
    public function getPresent()
    {
        if (!isset($this->_presentObject))
        {
            static::$_presentInstances[static::class] = $this;
            $this->_presentObject = $this->makePresent();
        }

        return $this->_presentObject;
    }

    protected static $_presentInstances = [];

    public static function getPresentInstance() : Model
    {
        return static::$_presentInstances[static::class] ??= new static;
    }

    /**
     * Get present attribute
     *
     * @param string      $name
     * @param string|null $get
     * @return mixed|Attribute
     */
    public static function attr(string $name, ?string $get = null)
    {
        if (isset($get))
        {
            return static::getPresentInstance()->getPresent()->getAttribute($name)->{'get' . $get}();
        }
        else
        {
            return static::getPresentInstance()->getPresent()->getAttribute($name);
        }
    }


    public function initializeHasPresent()
    {
        $present = $this->getPresent();

        $this->mergeFillable($present->fillable);
        $this->mergeCasts($present->casts);
        $this->makeHidden($present->hidden);
    }


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


    /**
     * Get file value
     *
     * @param string $attribute
     * @return Types\File
     */
    public function file(string $attribute)
    {
        $attr = $this->getPresent()->getAttribute($attribute);
        if ($attr instanceof FileColumn)
        {
            return $attr->getFileValue($this->getAttribute($attribute), $this);
        }

        throw new \InvalidArgumentException("Attribute [{$attribute}] should be a File");
    }

}