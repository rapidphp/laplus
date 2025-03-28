<?php

namespace Rapid\Laplus\Label;

use Carbon\Carbon;
use Closure;

class TranslateFactory
{
    protected ?string $undefinedLabel = null;
    protected ?string $trueLabel = null;
    protected ?string $falseLabel = null;
    protected ?string $onLabel = null;
    protected ?string $offLabel = null;
    protected ?string $yesLabel = null;
    protected ?string $noLabel = null;
    protected ?Closure $dateLabel = null;
    protected ?Closure $timeLabel = null;
    protected ?Closure $dateTimeLabel = null;

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

    public function setUndefinedLabel(?string $label): void
    {
        $this->undefinedLabel = $label;
    }

    public function getUndefinedLabel(): string
    {
        return $this->undefinedLabel ?? __("laplus::label.undefined");
    }

    public function setTrueLabel(?string $label): void
    {
        $this->trueLabel = $label;
    }

    public function getTrueLabel(): string
    {
        return $this->trueLabel ?? __("laplus::label.true");
    }

    public function setFalseLabel(?string $label): void
    {
        $this->falseLabel = $label;
    }

    public function getFalseLabel(): string
    {
        return $this->falseLabel ?? __("laplus::label.false");
    }

    public function setOnLabel(?string $label): void
    {
        $this->onLabel = $label;
    }

    public function getOnLabel(): string
    {
        return $this->onLabel ?? __("laplus::label.on");
    }

    public function setOffLabel(?string $label): void
    {
        $this->offLabel = $label;
    }

    public function getOffLabel(): string
    {
        return $this->offLabel ?? __("laplus::label.off");
    }

    public function setYesLabel(?string $label): void
    {
        $this->yesLabel = $label;
    }

    public function getYesLabel(): string
    {
        return $this->yesLabel ?? __("laplus::label.yes");
    }

    public function setNoLabel(?string $label): void
    {
        $this->noLabel = $label;
    }

    public function getNoLabel(): string
    {
        return $this->noLabel ?? __("laplus::label.no");
    }

    public function setDateLabel(?Closure $label): void
    {
        $this->dateLabel = $label;
    }

    public function getDateLabel(Carbon $carbon): string
    {
        return value($this->dateLabel, $carbon) ?? $carbon->toDateString();
    }

    public function setTimeLabel(?Closure $label): void
    {
        $this->timeLabel = $label;
    }

    public function getTimeLabel(Carbon $carbon): string
    {
        return value($this->timeLabel, $carbon) ?? $carbon->toTimeString();
    }

    public function setDateTimeLabel(?Closure $label): void
    {
        $this->dateTimeLabel = $label;
    }

    public function getDateTimeLabel(Carbon $carbon): string
    {
        return value($this->dateTimeLabel, $carbon) ?? $carbon->toDateTimeString();
    }
}