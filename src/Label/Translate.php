<?php

namespace Rapid\Laplus\Label;

use Carbon\Carbon;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string|null tryTranslateSpecials($value, ?LabelTranslator $translator = null) Try to translate specials, like null, true and false.
 * @method static mixed translateDeep($value, array $args) Translate objects recursive.
 * @method static string getUndefinedLabel()
 * @method static string getTrueLabel()
 * @method static string getFalseLabel()
 * @method static string getOnLabel()
 * @method static string getOffLabel()
 * @method static string getYesLabel()
 * @method static string getNoLabel()
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