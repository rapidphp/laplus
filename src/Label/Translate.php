<?php

namespace Rapid\Laplus\Label;

use Carbon\Carbon;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null tryTranslateSpecials($value, ?LabelTranslator $translator = null) Try to translate specials, like object, null, true and false.
 * @method static string getUndefinedLabel()
 * @method static string getTrueLabel()
 * @method static string getFalseLabel()
 * @method static string getDateLabel(Carbon $carbon)
 * @method static string getTimeLabel(Carbon $carbon)
 * @method static string getDateTimeLabel(Carbon $carbon)
 */
class Translate extends Facade
{

    protected static function getFacadeAccessor()
    {
        return TranslateFactory::class;
    }

}