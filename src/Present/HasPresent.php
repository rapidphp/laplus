<?php

namespace Rapid\Laplus\Present;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Rapid\Laplus\Present\Attributes\Attribute;

trait HasPresent
{

    protected static array $_presentObjects = [];

    // /**
    //  * Present the model inline
    //  *
    //  * @return void
    //  */
    // public function present(Present $present)
    // {
    // }

    /**
     * List of present extensions
     *
     * @var array
     */
    protected static array $_presentExtends = [];

    /**
     * Extend the presentation
     *
     * @param string|PresentExtension|Closure $extension
     * @return void
     */
    protected static function extendPresent(string|PresentExtension|Closure $extension) : void
    {
        if (is_string($extension))
        {
            $extension = new $extension;
        }

        @static::$_presentExtends[static::class][] = $extension;
    }

    /**
     * Get the list of present extensions
     *
     * @return array
     */
    public static function getPresentExtensions() : array
    {
        return static::$_presentExtends[static::class] ?? [];
    }

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
    public function getPresent() : Present
    {
        if (!isset(static::$_presentObjects[static::class]))
        {
            static::$_presentInstances[static::class] ??= $this;
            return static::$_presentObjects[static::class] = $this->makePresent();
        }

        return static::$_presentObjects[static::class];
    }

    protected static array $_presentInstances = [];

    public static function getPresentableInstance() : static
    {
        return static::$_presentInstances[static::class] ??= new static;
    }

    public static function getStaticPresentInstance() : Present
    {
        return static::getPresentableInstance()->getPresent();
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
            return static::getStaticPresentInstance()->getAttribute($name)->{'get' . $get}();
        }
        else
        {
            return static::getStaticPresentInstance()->getAttribute($name);
        }
    }


    public function initializeHasPresent()
    {
        $present = $this->getPresent();

        $this->mergeFillable($present->fillable);
        $this->mergeCasts($present->casts);
        $this->makeHidden($present->hidden);
    }

}