<?php

namespace Rapid\Laplus\Travel;

abstract class Travel
{
    public string|array $on;
    public string|array $whenAdded = [];
    public string|array $whenRemoving = [];
    public array $whenRenamed = [];
    public bool $anywayBefore = false;
    public bool $anywayFinally = false;

    abstract public function up(): void;

    abstract public function down(): void;

    public function getTables(): array
    {
        return array_filter(
            array_map(function (string $class) {
                if (str_contains($class, '\\')) {
                    return class_exists($class) ? app($class)->getTable() : null;
                } else {
                    return $class;
                }
            }, (array)$this->on),
        );
    }

    final public function trashed(string $column): string
    {
        return "{$column}_trashed";
    }
}