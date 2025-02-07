<?php

namespace Rapid\Laplus\Editors;

class GitIgnoreEditor
{
    protected array $lines;
    protected bool $isChanged = false;

    public function __construct(
        public readonly string $path,
        bool                   $createIfNotExists = true,
    )
    {
        if (file_exists($path)) {
            if ($createIfNotExists) {
                touch($path);
            } else {
                throw new \RuntimeException("File [$path] is not exists.");
            }
        }

        $this->lines = file($path);
    }

    public static function tryMake(string $path): ?static
    {
        if (!file_exists($path)) {
            return null;
        }

        return new static($path);
    }

    public static function make(string $path, bool $createIfNotExists = true): static
    {
        return new static($path, $createIfNotExists);
    }

    public function add(string $line): static
    {
        if (!$this->has($line)) {
            $this->lines[] = $line;
            $this->isChanged = true;
        }

        return $this;
    }

    public function remove(string $line): static
    {
        if ($this->has($line)) {
            $this->lines = array_filter($this->lines, fn(string $line2) => $line2 !== $line);
            $this->isChanged = true;
        }

        return $this;
    }

    public function has(string $line): bool
    {
        return in_array($line, $this->lines);
    }

    public function save(): void
    {
        file_put_contents($this->path, implode("\n", $this->lines));
    }
}