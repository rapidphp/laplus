<?php

namespace Rapid\Laplus\Label;

use Carbon\Carbon;

class TranslateFactory
{

    /**
     * Translate objects recursive.
     *
     * @param       $value
     * @param array $args
     * @return mixed
     */
    public function translateDeep($value, array $args): mixed
    {
        while (is_object($value) && method_exists($value, 'getTranslatedLabel')) {
            $value = $value->getTranslatedLabel(...$args);
        }

        return $value;
    }

    /**
     * Try to translate specials, like null, true and false.
     *
     * @param                      $value
     * @param LabelTranslator|null $translator
     * @return string|null
     */
    public function tryTranslateSpecials($value, ?LabelTranslator $translator = null): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            if ($value === null) {
                return $translator ? $translator->undefined : $this->getUndefinedLabel();
            } elseif ($value === true) {
                return $translator ? $translator->true : $this->getTrueLabel();
            } elseif ($value === false) {
                return $translator ? $translator->false : $this->getFalseLabel();
            }

            return null;
        }

        return (string)$value;
    }


    public function getUndefinedLabel(): string
    {
        return __("laplus::label.undefined");
    }

    public function getTrueLabel(): string
    {
        return __("laplus::label.true");
    }

    public function getFalseLabel(): string
    {
        return __("laplus::label.false");
    }

    public function getOnLabel(): string
    {
        return __("laplus::label.on");
    }

    public function getOffLabel(): string
    {
        return __("laplus::label.off");
    }

    public function getYesLabel(): string
    {
        return __("laplus::label.yes");
    }

    public function getNoLabel(): string
    {
        return __("laplus::label.no");
    }

    public function getDateLabel(Carbon $carbon): string
    {
        return $carbon->toDateString();
    }

    public function getTimeLabel(Carbon $carbon): string
    {
        return $carbon->toTimeString();
    }

    public function getDateTimeLabel(Carbon $carbon): string
    {
        return $carbon->toDateTimeString();
    }

}