<?php

namespace Rapid\Laplus\Support\Traits;

use Rapid\Laplus\Validation\PendingRules;

trait HasRules
{
    public static function rules(): PendingRules
    {
        return new PendingRules(
            model: static::class,
            callback: static::getStaticPresentInstance()->getRules(...),
        );
    }
}