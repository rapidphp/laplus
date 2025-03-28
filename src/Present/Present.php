<?php

namespace Rapid\Laplus\Present;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Present\Attributes\Attribute;
use Rapid\Laplus\Present\Attributes\Index;
use Rapid\Laplus\Travel\Travel;

abstract class Present
{
    use Macroable;
    use Concerns\Columns,
        Concerns\Relations,
        Concerns\Generations,
        Concerns\Indexes;

    private static array $presents_model_cache = [];
    private static array $presents_class_cache = [];
    public array $fillable;
    public array $hidden;
    public array $casts;
    public array $getters;
    public array $setters;
    protected bool $isYielded = false;
    protected array $parentYieldStack = [];

    /**
     * List of attributes
     *
     * @var Attribute[]
     */
    protected array $attributes = [];

    /**
     * List of indexes
     *
     * @var Index[]
     */
    protected array $indexes = [];

    /**
     * List of travels
     *
     * @var Travel[]
     */
    protected array $travels = [];

    /**
     * List of callable extensions
     *
     * @var Closure[]
     */
    protected array $callableExtensions = [];

    /**
     * List of hook extensions
     *
     * @var PresentExtension[]
     */
    protected array $extensions = [];

    public function __construct(
        public Model $instance,
    )
    {
        $this->mergeExtensions($this->extensions());
    }

    /**
     * Collect present information
     *
     * @return void
     */
    public function collectPresent(): void
    {
        $this->fillable = [];
        $this->hidden = [];
        $this->casts = [];
        $this->getters = [];
        $this->setters = [];
        $this->travels = [];

        $this->isYielded = false;

        foreach ($this->extensions as $extension) {
            $extension->before($this);
        }

        $this->present();
        $this->yield();

        foreach ($this->extensions as $extension) {
            $extension->after($this);
        }

        foreach ($this->attributes as $attribute) {
            if ($attribute->isFillable()) {
                $this->fillable[] = $attribute->name;
            }
            if ($attribute->isHidden()) {
                $this->hidden[] = $attribute->name;
            }
            if ($cast = $attribute->getCast()) {
                $this->casts[$attribute->name] = $cast;
            }
            if ($getter = $attribute->getGetter()) {
                $this->getters[$attribute->name] = $getter;
            }
            if ($setter = $attribute->getSetter()) {
                $this->setters[$attribute->name] = $setter;
            }

            $attribute->boot($this);
        }

        foreach ($this->extensions as $extension) {
            $extension->finally($this);
        }
    }

    public function mergeExtensions(array $extensions): void
    {
        foreach ($extensions as $extension) {
            if (is_string($extension)) {
                $this->extensions[] = new $extension;
            } elseif ($extension instanceof Closure) {
                $this->callableExtensions[] = $extension;
            } else {
                $this->extensions[] = $extension;
            }
        }
    }

    /**
     * Get the extensions
     *
     * @return PresentExtension[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Get default extensions
     *
     * @return array
     */
    protected function extensions(): array
    {
        return [];
    }

    /**
     * Present the model
     *
     * @return void
     */
    protected abstract function present();

    /**
     * Apply the present extensions
     *
     * @return void
     */
    public function yield(): void
    {
        if ($this->parentYieldStack) {
            array_pop($this->parentYieldStack)();
        } else {
            if ($this->isYielded) return;

            $this->isYielded = true;

            foreach ($this->callableExtensions as $extension) {
                $extension($this);
            }

            foreach ($this->extensions as $extension) {
                $extension->extend($this);
            }
        }
    }

    /**
     * Get present from a model (cached result)
     *
     * @param Model $model
     * @return Present
     */
    public static function getPresentOfModel(Model $model)
    {
        return static::$presents_model_cache[get_class($model)] ??= static::makePresentOfModel($model);
    }

    /**
     * Make present for a model
     *
     * @param Model $model
     * @return Present
     */
    private static function makePresentOfModel(Model $model)
    {
        $modelClass = get_class($model);
        if (str_contains($modelClass, '\\Models\\')) {
            $before = Str::beforeLast($modelClass, '\\Models\\');
            $after = Str::afterLast($modelClass, '\\Models\\');

            $present = "{$before}\\Presents\\{$after}Present";
        } elseif (str_contains($modelClass, '\\')) {
            $before = Str::beforeLast($modelClass, '\\');
            $after = Str::afterLast($modelClass, '\\');

            $present = "{$before}\\Presents\\{$after}Present";
        } else {
            $present = "Presents\\{$modelClass}Present";
        }

        return new $present($model);
    }

    /**
     * Get present of a type (cached result)
     *
     * @param Model $model
     * @param string $class
     * @return Present
     */
    public static function getPresentOfType(Model $model, string $class)
    {
        return static::$presents_model_cache[$class] ??= new $class($model);
    }

    /**
     * Make inline present
     *
     * @param Model $instance
     * @param       $callback `function(Present $present)`
     * @return InlinePresent
     */
    public static function inline(Model $instance, $callback)
    {
        return new InlinePresent($instance, $callback);
    }

    /**
     * Call the parent by replacing `yield()` with `$extend`
     *
     * @param Closure $parent
     * @param Closure $extend
     * @return void
     */
    public function atYield(Closure $parent, Closure $extend)
    {
        $extend = fn() => $extend($this);

        $this->parentYieldStack[] = $extend;

        $parent($this);

        if ($this->parentYieldStack && end($this->parentYieldStack) == $extend) {
            $this->yield();
        }
    }

    /**
     * Extend the present with an extension
     *
     * @param string|PresentExtension $extension
     * @return void
     */
    public function extend(string|PresentExtension $extension)
    {
        if (is_string($extension)) {
            $extension = new $extension;
        }

        $extension($this);
    }

    /**
     * Create new attribute related to another attribute
     *
     * @param string $attribute
     * @param string $for
     * @param ?callable|Closure($value, Model $model, string $key):mixed $get
     * @param ?callable|Closure($value, Model $model, string $key):mixed $set
     * @return Attribute
     */
    public function attributeFor(string $attribute, string $for, $get = null, $set = null)
    {
        $attr = $this->attribute($attribute);

        if (isset($get)) {
            $attr->getUsingModel(fn(Model $model, string $key) => $get($model->getAttribute($for), $model, $key));
        }

        if (isset($set)) {
            $attr->setUsing(fn($value, Model $model, string $key) => $model->setAttribute($for, $set($value, $model, $key)));
        }

        return $attr;
    }

    /**
     * Create new attribute
     *
     * @template T
     * @param string|Attribute|T $attribute
     * @param ?callable|Closure(Model $model, string $key):mixed $get
     * @param ?callable|Closure($value, Model $model, string $key):mixed $set
     * @return Attribute|T
     */
    public function attribute($attribute, $get = null, $set = null)
    {
        if (is_string($attribute)) {
            $attribute = new Attribute($attribute);
        }

        if (array_key_exists($attribute->name, $this->attributes)) {
            throw new \InvalidArgumentException("Duplicated attribute name [$attribute->name]");
        }

        if (isset($get)) {
            $attribute->getUsingModel($get);
        }

        if (isset($set)) {
            $attribute->setUsing($set);
        }

        $this->attributes[$attribute->name] = $attribute;
        return $attribute;
    }

    /**
     * Get an attribute
     *
     * @param string $name
     * @return ?Attribute
     */
    public function getAttribute(string $name): ?Attribute
    {
        return @$this->attributes[$name];
    }

    /**
     * Get all the attributes
     *
     * @return Attribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Create new attribute
     *
     * @template T
     * @param Index|T $index
     * @return Index|T
     */
    public function addIndex(Index $index)
    {
        if (array_key_exists($index->name, $this->attributes)) {
            throw new \InvalidArgumentException("Duplicated attribute name [$index->name]");
        }

        $this->indexes[$index->name] = $index;
        return $index;
    }

    /**
     * Get docblock comments to present the attribute to IDE
     *
     * @param GuideScope $scope
     * @return array
     * @internal
     */
    public function docblock(GuideScope $scope): array
    {
        $doc = [];
        foreach ($this->attributes as $attribute) {
            array_push($doc, ...$attribute->docblock($scope));
        }

        foreach ($this->extensions as $extension) {
            array_push($doc, ...$extension->docblock($this, $scope));
        }

        return $doc;
    }

    /**
     * Generate database structure
     *
     * @return void
     */
    protected function generate(): void
    {
        $this->table($this->getTable(), $this->collectPresent(...));
    }

    /**
     * Get table name
     *
     * @return string|null
     */
    public function getTable(): ?string
    {
        return $this->instance->getTable();
    }
}