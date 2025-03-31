<?php

namespace Rapid\Laplus\Validation;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use IteratorAggregate;
use Rapid\Laplus\Validation\Rules\Unique;
use Traversable;

class PendingRules implements Arrayable, IteratorAggregate
{
    public const FOR_CREATE = 1;
    public const FOR_UPDATE = 2;
    public const FOR_FIND = 3;

    protected array $only = [];
    protected array $except = [];
    protected array $formatKey = [];
    protected int $for;
    protected Model|int|string $ignore;

    public function __construct(
        protected string  $model,
        protected Closure $callback,
    )
    {
    }

    public function only(string|array $key)
    {
        $this->only = array_merge($this->only, (array)$key);

        return $this;
    }

    public function except(string|array $key)
    {
        $this->except = array_merge($this->except, (array)$key);

        return $this;
    }

    public function formatKey(Closure|array $callback)
    {
        if (is_array($callback)) {
            $callback = static function (string $key) use ($callback) {
                return $callback[$key] ?? $key;
            };
        }

        $this->formatKey[] = $callback;
        return $this;
    }

    public function snakeCase()
    {
        return $this->formatKey(static fn(string $key) => Str::snake($key));
    }

    public function kebabCase()
    {
        return $this->formatKey(static fn(string $key) => str_replace('_', '-', Str::kebab($key)));
    }

    public function camelCase()
    {
        return $this->formatKey(static fn(string $key) => Str::camel($key));
    }

    public function pascalCase()
    {
        return $this->formatKey(static fn(string $key) => ucfirst(Str::camel($key)));
    }

    public function upperCase()
    {
        return $this->formatKey(static fn(string $key) => Str::upper($key));
    }

    public function lowerCase()
    {
        return $this->formatKey(static fn(string $key) => Str::lower($key));
    }

    public function forCreate()
    {
        $this->for = self::FOR_CREATE;

        return $this;
    }

    public function forUpdate(Model|int|string $ignore)
    {
        $this->for = self::FOR_UPDATE;
        $this->ignore = $ignore;

        return $this;
    }

    public function forFind()
    {
        $this->for = self::FOR_FIND;

        return $this;
    }

    public function toArray(): array
    {
        $rules = (array)app()->call($this->callback);

        if ($this->only) {
            $rules = Arr::only($rules, $this->only);
        }

        if ($this->except) {
            $rules = Arr::except($rules, $this->except);
        }

        if ($this->formatKey) {
            $newRules = [];
            foreach ($rules as $key => $rule) {
                $newRules[$this->reformatKey($key)] = $rule;
            }
            $rules = $newRules;
        }

        foreach ($rules as $key => $rule) {
            if (is_array($rule)) {
                $rules[$key] = $this->reformatArrayRules($rule);
            }
        }

        return $rules;
    }

    protected function reformatKey(string $key): string
    {
        foreach ($this->formatKey as $callback) {
            $key = $callback($key);
        }

        return $key;
    }

    protected function reformatArrayRules(array $rules): array
    {
        foreach ($rules as $index => $rule) {
            if (is_a($rule, Unique::class, true)) {
                if (!isset($this->for) || $this->for === self::FOR_CREATE) {
                    $rule = Rule::unique($this->model);
                } elseif ($this->for === self::FOR_UPDATE) {
                    $rule = Rule::unique($this->model)->ignore($this->ignore);
                } elseif ($this->for === self::FOR_FIND) {
                    $rule = Rule::exists($this->model);
                }
            }

            $rules[$index] = $rule;
        }

        return $rules;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->toArray());
    }
}