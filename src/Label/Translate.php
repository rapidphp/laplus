<?php

namespace Rapid\Laplus\Label;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null tryTranslateSpecials($value, ?LabelTranslator $translator = null) Try to translate specials, like null, true and false.
 * @method static mixed translateDeep($value, array $args) Translate objects recursive.
 * @method static void setUndefinedLabel(?string $label)
 * @method static string getUndefinedLabel()
 * @method static void setTrueLabel(?string $label)
 * @method static string getTrueLabel()
 * @method static void setFalseLabel(?string $label)
 * @method static string getFalseLabel()
 * @method static void setOnLabel(?string $label)
 * @method static string getOnLabel()
 * @method static void setOffLabel(?string $label)
 * @method static string getOffLabel()
 * @method static void setYesLabel(?string $label)
 * @method static string getYesLabel()
 * @method static void setNoLabel(?string $label)
 * @method static string getNoLabel()
 * @method static void setDateLabel(?Closure $label)
 * @method static string getDateLabel(Carbon $carbon)
 * @method static void setTimeLabel(?Closure $label)
 * @method static string getTimeLabel(Carbon $carbon)
 * @method static void setDateTimeLabel(?Closure $label)
 * @method static string getDateTimeLabel(Carbon $carbon)
 */
class Translate extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TranslateFactory::class;
    }
}