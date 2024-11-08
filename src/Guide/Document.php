<?php

namespace Rapid\Laplus\Guide;

class Document
{

    public static function object(mixed $object) : string
    {
        return match (gettype($object))
        {
            'string'            => "\"" . addslashes($object) . "\"",
            'boolean'           => $object ? 'true' : 'false',
            // 'NULL'              => 'null',
            'integer', 'double' => "$object",
            'array'             => static::array($object),
            default             => 'null',
        };
    }

    public static function array(array $array) : string
    {
        $doc = [];
        $i = 0;
        foreach ($array as $key => $value)
        {
            if (is_int($key) && $key == $i)
            {
                $doc[] = static::object($value);
                $i++;
            }
            else
            {
                $doc[] = static::object($key) . ' => ' . static::object($value);
            }
        }

        return '[' . implode(', ', $doc) . ']';
    }

}