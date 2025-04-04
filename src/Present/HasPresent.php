<?php

namespace Rapid\Laplus\Present;

use Closure;

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
    protected static array $_presentInstances = [];

    public static function getStaticPresentInstance(): Present
    {
        return static::getPresentableInstance()->getPresent();
    }

    /**
     * Get the present instance (value will be cached)
     *
     * @return Present
     */
    public function getPresent(): Present
    {
        if (!isset(static::$_presentObjects[static::class])) {
            static::$_presentInstances[static::class] ??= $this;

            $present = $this->makePresent();
            $present->mergeExtensions(static::$_presentExtends[static::class] ?? []);
            $present->collectPresent();

            return static::$_presentObjects[static::class] = $present;
        }

        return static::$_presentObjects[static::class];
    }

    /**
     * Get the present object
     *
     * @return Present
     */
    protected function makePresent(): Present
    {
        if (method_exists($this, 'present')) {
            return Present::inline($this, $this->present(...));
        }

        if ($class = $this->getPresentClass()) {
            return Present::getPresentOfType($this, $class);
        }

        return Present::getPresentOfModel($this);
    }

    /**
     * Get the present class name
     *
     * @return string|null
     */
    protected function getPresentClass(): ?string
    {
        return property_exists($this, 'presentClass') ? $this->presentClass : null;
    }

    public static function getPresentableInstance(): static
    {
        return static::$_presentInstances[static::class] ??= new static;
    }

    /**
     * Extend the presentation
     *
     * @param string|PresentExtension|Closure $extension
     * @return void
     */
    protected static function extendPresent(string|PresentExtension|Closure $extension): void
    {
        @static::$_presentExtends[static::class][] = $extension;
    }

    public function initializeHasPresent()
    {
        $present = $this->getPresent();

        $this->mergeFillable($present->fillable);
        $this->mergeCasts($present->casts);
        $this->makeHidden($present->hidden);
    }

    public function shouldIgnore(): bool
    {
        return property_exists($this, 'shouldIgnore') ? $this->shouldIgnore : false;
    }

}