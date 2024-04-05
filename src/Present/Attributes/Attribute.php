<?php

namespace Rapid\Laplus\Present\Attributes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Mockery\Matcher\Closure;
use Rapid\Laplus\Present\Generator;
use Rapid\Laplus\Present\Present;
use Rapid\Laplus\Present\PresentAttributeCast;

class Attribute
{

    public function __construct(
        public string $name,
    )
    {
    }

    protected bool $fillable = false;

    public function isFillable()
    {
        return $this->fillable;
    }

    public function fillable(bool $value = true)
    {
        $this->fillable = $value;
        return $this;
    }

    protected $cast;
    protected $castUsing;

    public function getCast()
    {
        return $this->cast;
    }

    public function getCastUsing()
    {
        return $this->castUsing;
    }

    public function cast($castType)
    {
        $this->cast = $castType;
        return $this;
    }

    public function noCast()
    {
        $this->cast = null;
        return $this;
    }

    public function castUsing($get, $set)
    {
        $this->castUsing = ['get' => $get, 'set' => $set];
        return $this->cast(PresentAttributeCast::class);
    }

    public function setValue($value)
    {
        
    }

    public function getValue($value)
    {

    }


    protected $getter;
    protected $setter;

    /**
     * @param callable|Closure($value, Model $model, string $key, array $attributes):mixed $callback
     * @return $this
     */
    public function getUsing($callback)
    {
        $this->getter = $callback;
        return $this;
    }

    /**
     * @param callable|Closure($value, Model $model, string $key, array $attributes):mixed $callback
     * @return $this
     */
    public function setUsing($callback)
    {
        $this->setter = $callback;
        return $this;
    }

    /**
     * @param callable|Closure(Model $model, string $key):mixed $callback
     * @return $this
     */
    public function getUsingModel($callback)
    {
        return $this->getUsing(fn($_, $model, $key) => $callback($model, $key));
    }

    public function getGetter()
    {
        return $this->getter;
    }

    public function getSetter()
    {
        return $this->setter;
    }


    public function boot(Present $present)
    {
    }

    public function generate(Present $present)
    {
    }

}