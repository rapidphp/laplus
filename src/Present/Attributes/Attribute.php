<?php

namespace Rapid\Laplus\Present\Attributes;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Traits\Macroable;
use Rapid\Laplus\Guide\GuideScope;
use Rapid\Laplus\Label\LabelTypeException;
use Rapid\Laplus\Label\Translate;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Present\PresentAttributeCast;

class Attribute
{
    use Macroable;

    protected bool $fillable = false;
    protected bool $hidden = false;
    protected $cast;
    protected $castUsing;
    protected $getter;
    protected $setter;
    protected $labelUsing = null;
    protected string $typeHint;
    protected string $docHint;

    public function __construct(
        public string $name,
    )
    {
    }

    public function isFillable()
    {
        return $this->fillable;
    }

    /**
     * Remove attribute fillable
     *
     * @return $this
     */
    public function notFillable()
    {
        $this->fillable = false;
        return $this;
    }

    /**
     * Set attribute fillable
     *
     * @param bool $value
     * @return $this
     */
    public function fillable(bool $value = true)
    {
        $this->fillable = $value;
        return $this;
    }

    /**
     * Set attribute visibility hidden
     *
     * @param bool $value
     * @return $this
     */
    public function hidden(bool $value = true)
    {
        $this->hidden = $value;
        return $this;
    }

    public function isHidden()
    {
        return $this->hidden;
    }

    public function getCast()
    {
        return $this->cast;
    }

    public function getCastUsing()
    {
        return $this->castUsing;
    }

    /**
     * Remove casting type
     *
     * @return $this
     */
    public function noCast()
    {
        $this->cast = null;
        return $this;
    }

    /**
     * Set casting using two callable
     *
     * @param callable $get
     * @param callable $set
     * @return $this
     */
    public function castUsing(callable $get, callable $set)
    {
        $this->castUsing = ['get' => $get, 'set' => $set];
        return $this->cast(PresentAttributeCast::class);
    }

    /**
     * Set casting type
     *
     * @param $castType
     * @return $this
     */
    public function cast($castType)
    {
        $this->cast = $castType;
        return $this;
    }

    /**
     * Define getter using callback that given the model
     *
     * @param callable|Closure(Model $model, string $key):mixed $callback
     * @return $this
     */
    public function getUsingModel($callback)
    {
        return $this->getUsing(fn($_, $model, $key) => $callback($model, $key));
    }

    /**
     * Define getter using callback
     *
     * @param callable|Closure($value, Model $model, string $key, array $attributes):mixed $callback
     * @return $this
     */
    public function getUsing($callback)
    {
        $this->getter = $callback;
        return $this;
    }

    /**
     * Make attribute readonly that throw an exception on set.
     *
     * @return $this
     */
    public function readonly()
    {
        return $this->setUsing(fn() => throw new Exception("Failed to set readonly attribute [$this->name]"));
    }

    /**
     * Define setter using callback
     *
     * @param callable|Closure($value, Model $model, string $key, array $attributes):mixed $callback
     * @return $this
     */
    public function setUsing($callback)
    {
        $this->setter = $callback;
        return $this;
    }

    /**
     * Make attribute setonly that throw an exception on get.
     *
     * @return $this
     */
    public function setonly()
    {
        return $this->getUsing(fn() => throw new Exception("Failed to get setonly attribute [$this->name]"));
    }

    public function getGetter()
    {
        return $this->getter;
    }

    public function getSetter()
    {
        return $this->setter;
    }

    /**
     * Define attribute label (it's not recommended)
     *
     * @param string|Closure $callback
     * @return $this
     */
    public function label(string|Closure $callback)
    {
        $this->labelUsing = $callback;
        return $this;
    }

    public function getLabelFor($value, array $args): string
    {
        $value = isset($this->labelUsing) ? value($this->labelUsing, $value, ...$args) : $value;

        $value = Translate::translateDeep($value, $args);
        $translated = Translate::tryTranslateSpecials($value);

        if ($translated === null) {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new LabelTypeException(
                sprintf("Label [%s] got as [%s], expected [string]", $this->name, $type),
            );
        }

        return $translated;
    }

    /**
     * Boots the attribute
     *
     * @param Present $present
     * @return void
     */
    public function boot(Present $present)
    {
    }

    /**
     * Generate database structure
     *
     * @param Present $present
     * @return void
     */
    public function generate(Present $present)
    {
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
        return [];
    }

    /**
     * Set the document hint comment (if available)
     *
     * @param string $comment
     * @param string|null $typeHint
     * @return $this
     */
    public function docHint(string $comment, ?string $typeHint = null)
    {
        $this->docHint = str_replace("\n", ". ", $comment);

        if (isset($typeHint)) {
            $this->typeHint($typeHint);
        }

        return $this;
    }

    /**
     * Set the type hint (if available)
     *
     * @param string $typeHint
     * @return $this
     */
    public function typeHint(string $typeHint)
    {
        $this->typeHint = $typeHint;
        return $this;
    }

}