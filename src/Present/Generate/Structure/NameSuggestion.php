<?php

namespace Rapid\Laplus\Present\Generate\Structure;

class NameSuggestion
{
    public const ADD = 'add';
    public const REMOVE = 'remove';
    public const SOFT_REMOVE = 'soft_remove';
    public const RENAME = 'rename';
    public const CHANGE = 'change';

    public array $suggestions = [];

    public function add(string $tag, string $column, string $name): void
    {
        @$this->suggestions[$column][$tag] = $name;
    }

    public function addAdd(string $column, string $name): void
    {
        $this->add(self::ADD, $column, $name);
    }

    public function addRemove(string $column, string $name): void
    {
        $this->add(self::REMOVE, $column, $name);
    }

    public function addSoftRemove(string $column, string $name): void
    {
        $this->add(self::SOFT_REMOVE, $column, $name);
    }

    public function addRename(string $column, string $name): void
    {
        $this->add(self::RENAME, $column, $name);
    }

    public function addChange(string $column, string $name): void
    {
        $this->add(self::CHANGE, $column, $name);
    }

    public function has(string $column, ?string $tag = null): bool
    {
        if (isset($tag)) {
            return isset($this->suggestions[$column][$tag]);
        } else {
            return (bool)@$this->suggestions[$column];
        }
    }

    public function removeAll(string $tag): void
    {
        foreach (array_keys($this->suggestions) as $column) {
            unset($this->suggestions[$column][$tag]);
        }
    }

    public function moveTagFrom(NameSuggestion $suggestion, string $tag): void
    {
        foreach (array_keys($suggestion->suggestions) as $column) {
            if (isset($suggestion->suggestions[$column][$tag])) {
                $this->add($tag, $column, $suggestion->suggestions[$column][$tag]);
            }

            unset($suggestion->suggestions[$column][$tag]);
        }
    }

    public function get(): ?string
    {
        if (count($this->suggestions) === 1 && count(head($this->suggestions)) === 1) {
            return head(head($this->suggestions));
        }

        return null;
    }
}