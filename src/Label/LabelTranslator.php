<?php

namespace Rapid\Laplus\Label;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionMethod;

/**
 * @property-read string $undefined Undefined label
 * @property-read string $false False label
 * @property-read string $true True label
 * @property-read ?string $asDate Get latest attribute as date format
 * @property-read ?string $asTime Get latest attribute as time format
 * @property-read ?string $asDateTime Get latest attribute as date & time format
 * @property-read string $latestAttribute Get latest attribute name
 * @property-read mixed $value Get latest attribute value
 */
class LabelTranslator
{

    public function __construct(
        public readonly Model $record,
    )
    {
    }


    public function createdAt()
    {
        return $this->asDateTime;
    }

    public function updatedAt()
    {
        return $this->asDateTime;
    }


    public function hasLabel(string $name) : bool
    {
        try
        {
            return (new ReflectionMethod($this, Str::camel($name)))->isPublic();
        }
        catch (Exception $e)
        {
            return false;
        }
    }

    private array $_labelStack = [];

    public function getLabel(string $name, ...$args) : string
    {
        $this->_labelStack[] = Str::snake($name);
        $value = $this->{Str::camel($name)}(...$args);
        array_pop($this->_labelStack);

        $value = Translate::translateDeep($value, $args);
        $translated = Translate::tryTranslateSpecials($value, $this);

        if ($translated === null)
        {
            $type = is_object($value) ? get_class($value) : gettype($value);
            throw new LabelTypeException(
                sprintf("Label [%s] in [%s] returned [%s], expected [string]", $name, static::class, $type)
            );
        }

        return $translated;
    }

    public function __get(string $name)
    {
        if (method_exists($this, 'get' . $name))
        {
            return $this->{'get' . $name}();
        }

        throw new \InvalidArgumentException(sprintf("Property [%s] not found in [%s]", $name, static::class));
    }

    protected function getUndefined() : string
    {
        return Translate::getUndefinedLabel();
    }

    protected function getFalse() : string
    {
        return Translate::getFalseLabel();
    }

    protected function getTrue() : string
    {
        return Translate::getTrueLabel();
    }

    protected function getLatestAttribute() : string
    {
        return end($this->_labelStack);
    }

    protected function getValue() : mixed
    {
        return $this->record->getAttribute(
            $this->getLatestAttribute()
        );
    }

    protected function getAsDate() : ?string
    {
        return is_null($this->value) ? null : Translate::getDateLabel(new Carbon($this->value));
    }

    protected function getAsTime() : ?string
    {
        return is_null($this->value) ? null : Translate::getTimeLabel(new Carbon($this->value));
    }

    protected function getAsDateTime() : ?string
    {
        return is_null($this->value) ? null : Translate::getDateTimeLabel(new Carbon($this->value));
    }


    public static function makeLabelTranslatorFor(Model $record) : ?LabelTranslator
    {
        $modelClass = get_class($record);
        if (str_contains($modelClass, '\\Models\\'))
        {
            $before = Str::beforeLast($modelClass, '\\Models\\');
            $after = Str::afterLast($modelClass, '\\Models\\');

            $labelTranslator = "{$before}\\LabelTranslators\\{$after}LabelTranslator";
        }
        elseif (str_contains($modelClass, '\\'))
        {
            $before = Str::beforeLast($modelClass, '\\');
            $after = Str::afterLast($modelClass, '\\');

            $labelTranslator = "{$before}\\LabelTranslators\\{$after}LabelTranslator";
        }
        else
        {
            $labelTranslator = "LabelTranslators\\{$modelClass}LabelTranslator";
        }

        if (class_exists($labelTranslator))
        {
            return new $labelTranslator($record);
        }

        return null;
    }

}