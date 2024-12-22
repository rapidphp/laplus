<?php

namespace Rapid\Laplus\Guide;

use Illuminate\Support\Str;

class GuideScope
{

    public function __construct(
        public ?string $namespace,
        public array $uses,
    )
    {
    }


    /**
     * Convert type hint to writable type to the source
     *
     * @param string $type
     * @return string
     */
    public function typeHint(string $type)
    {
        return preg_replace_callback(
            '/[a-zA-Z0-9_\\\\]+/',
            function ($matches)
            {
                if (str_contains($matches[0], '\\') ||
                    class_exists($matches[0]) ||
                    interface_exists($matches[0]) ||
                    enum_exists($matches[0]))
                {
                    return $this->simplify('\\' . $matches[0]);
                }
                else
                {
                    return $matches[0];
                }
            },
            $type
        );
    }

    /**
     * Simplify the class using the namespace and used classes
     *
     * @param string $class
     * @return string
     */
    public function simplify(string $class) : string
    {
        $class = ltrim($class, '\\');

        if (array_key_exists($class, $this->uses))
        {
            return $this->uses[$class];
        }

        if (Str::contains($class, '\\'))
        {
            $namespace = Str::beforeLast($class, '\\');
            $className = Str::afterLast($class, '\\');

            if ($namespace === $this->namespace)
            {
                return $className;
            }
            elseif ($this->namespace && Str::startsWith($namespace, $this->namespace . '\\'))
            {
                return substr($namespace, strlen($this->namespace) + 1) . '\\' . $className;
            }
        }
        else
        {
            if ($this->namespace === null)
            {
                return $class;
            }
        }

        return '\\' . $class;
    }

    public function summary(string|false $comments) : ?string
    {
        if ($comments === false)
        {
            return null;
        }

        $comments = preg_replace('/^\/\*\*[\s\n*]*/', '', $comments);
        $comments = trim(explode("\n", $comments, 2)[0]);

        if (str_starts_with($comments, '@'))
        {
            return null;
        }

        return $comments;
    }

}