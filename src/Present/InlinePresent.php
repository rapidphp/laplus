<?php

namespace Rapid\Laplus\Present;

use Illuminate\Database\Eloquent\Model;

final class InlinePresent extends Present
{

    public function __construct(Model $instance, private $callback)
    {
        parent::__construct($instance);
    }

    /**
     * Make inline present
     *
     * @param Model $instance
     * @param       $callback `function(Present $present)`
     * @return InlinePresent
     */
    public static function make(Model $instance, $callback)
    {
        return new InlinePresent($instance, $callback);
    }

    /**
     * Present the model using callback
     *
     * @return void
     */
    protected function present()
    {
        ($this->callback)($this);
    }

}