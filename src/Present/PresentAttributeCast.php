<?php

namespace Rapid\Laplus\Present;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class PresentAttributeCast implements CastsAttributes
{

    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        $get = $model->getPresentObject()->getAttribute($key)->getCastUsing()['get'];
        return $get($value, $model, $key, $attributes);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        $set = $model->getPresentObject()->getAttribute($key)->getCastUsing()['set'];
        return $set($value, $model, $key, $attributes);
    }

}