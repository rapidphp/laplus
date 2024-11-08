<?php

namespace Rapid\Laplus\Present;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Traits\Macroable;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Present\Attributes\Attribute;
use Rapid\Laplus\Present\Attributes\Column;
use Rapid\Laplus\Present\Attributes\Index;

abstract class Present
{
    use Macroable;
    use Traits\Columns,
        Traits\Relations,
        Traits\Generations,
        Traits\Indexes;

    public function __construct(
        public Model $instance,
    )
    {
        $this->collectPresent();
    }

    public array $fillable;
    public array $hidden;
    public array $casts;
    public array $getters;
    public array $setters;

    /**
     * Collect present information
     *
     * @return void
     */
    public function collectPresent()
    {
        $this->fillable = [];
        $this->hidden = [];
        $this->casts = [];
        $this->getters = [];
        $this->setters = [];

        $this->present();
        $this->yield();

        foreach ($this->attributes as $attribute)
        {
            if ($attribute->isFillable())
            {
                $this->fillable[] = $attribute->name;
            }
            if ($attribute->isHidden())
            {
                $this->hidden[] = $attribute->name;
            }
            if ($cast = $attribute->getCast())
            {
                $this->casts[$attribute->name] = $cast;
            }
            if ($getter = $attribute->getGetter())
            {
                $this->getters[$attribute->name] = $getter;
            }
            if ($setter = $attribute->getSetter())
            {
                $this->setters[$attribute->name] = $setter;
            }

            $attribute->boot($this);
        }
    }

    /**
     * Present the model
     * 
     * @return void
     */
    protected abstract function present();

    /**
     * Present the table structure to generate
     *
     * @return void
     */
    protected function presentTable()
    {
        $this->present();
        $this->yield();
    }

    /**
     * Generate database structure
     *
     * @return void
     */
    protected function generate()
    {
        $this->table($this->getTable(), $this->presentTable(...));
    }

    /**
     * Get table name
     *
     * @return string|null
     */
    public function getTable()
    {
        return $this->instance->getTable();
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

        if ($this->parentYieldStack && end($this->parentYieldStack) == $extend)
        {
            $this->yield();
        }
    }

    protected bool $isYielded = false;

    protected array $parentYieldStack = [];

    /**
     * Apply the present extensions
     *
     * @return void
     */
    public function yield()
    {
        if ($this->parentYieldStack)
        {
            array_pop($this->parentYieldStack)();
        }
        else
        {
            if ($this->isYielded) return;

            $this->isYielded = true;

            foreach ($this->instance->getPresentExtensions() as $extension)
            {
                $extension($this);
            }
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
        if (is_string($extension))
        {
            $extension = new $extension;
        }

        $extension($this);
    }


    /**
     * List of attributes
     *
     * @var array<Attribute>
     */
    protected array $attributes = [];

    /**
     * Create new attribute
     *
     * @template T
     * @param string|Attribute|T $attribute
     * @param ?callable|Closure(Model $model, string $key):mixed  $get
     * @param ?callable|Closure($value, Model $model, string $key):mixed  $set
     * @return Attribute|T
     */
    public function attribute($attribute, $get = null, $set = null)
    {
        if (is_string($attribute))
        {
            $attribute = new Attribute($attribute);
        }

        if (array_key_exists($attribute->name, $this->attributes))
        {
            throw new \InvalidArgumentException("Duplicated attribute name [$attribute->name]");
        }

        if (isset($get))
        {
            $attribute->getUsingModel($get);
        }

        if (isset($set))
        {
            $attribute->setUsing($set);
        }

        $this->attributes[$attribute->name] = $attribute;
        return $attribute;
    }

    /**
     * Create new attribute related to another attribute
     *
     * @param string $attribute
     * @param string $for
     * @param ?callable|Closure($value, Model $model, string $key):mixed  $get
     * @param ?callable|Closure($value, Model $model, string $key):mixed  $set
     * @return Attribute
     */
    public function attributeFor(string $attribute, string $for, $get = null, $set = null)
    {
        $attr = $this->attribute($attribute);

        if (isset($get))
        {
            $attr->getUsingModel(fn(Model $model, string $key) => $get($model->getAttribute($for), $model, $key));
        }

        if (isset($set))
        {
            $attr->setUsing(fn($value, Model $model, string $key) => $model->setAttribute($for, $set($value, $model, $key)));
        }

        return $attr;
    }

    /**
     * Get an attribute
     *
     * @param string $name
     * @return ?Attribute
     */
    public function getAttribute(string $name)
    {
        return @$this->attributes[$name];
    }

    /**
     * List of indexes
     *
     * @var array<Index>
     */
    protected array $indexes = [];

    /**
     * Create new attribute
     *
     * @template T
     * @param Index|T $index
     * @return Index|T
     */
    public function addIndex(Index $index)
    {
        if (array_key_exists($index->name, $this->attributes))
        {
            throw new \InvalidArgumentException("Duplicated attribute name [$index->name]");
        }

        $this->indexes[$index->name] = $index;
        return $index;
    }



    

    private static $presents_model_cache = [];
    private static $presents_class_cache = [];

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
        if (str_contains($modelClass, '\\Models\\'))
        {
            $before = Str::beforeLast($modelClass, '\\Models\\');
            $after = Str::afterLast($modelClass, '\\Models\\');

            $present = "{$before}\\Presents\\{$after}Present";
        }
        elseif (str_contains($modelClass, '\\'))
        {
            $before = Str::beforeLast($modelClass, '\\');
            $after = Str::afterLast($modelClass, '\\');

            $present = "{$before}\\Presents\\{$after}Present";
        }
        else
        {
            $present = "Presents\\{$modelClass}Present";
        }

        return new $present($model);
    }

    /**
     * Get present of a type (cached result)
     *
     * @param Model  $model
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
     * Get docblock comments to present the attribute to IDE
     *
     * @param GuideScope $scope
     * @return array
     * @internal
     */
    public function docblock(GuideScope $scope) : array
    {
        $doc = [];
        foreach ($this->attributes as $attribute)
        {
            array_push($doc, ...$attribute->docblock($scope));
        }

        return $doc;
    }

}