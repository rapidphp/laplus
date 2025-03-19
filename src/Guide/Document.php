<?php

namespace Rapid\Laplus\Guide;

use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;

class Document
{

    public static function object(mixed $object): string
    {
        return match (gettype($object)) {
            'string'            => "\"" . addslashes($object) . "\"",
            'boolean'           => $object ? 'true' : 'false',
            // 'NULL'              => 'null',
            'integer', 'double' => "$object",
            'array'             => static::array($object),
            default             => 'null',
        };
    }

    public static function array(array $array): string
    {
        $doc = [];
        $i = 0;
        foreach ($array as $key => $value) {
            if (is_int($key) && $key == $i) {
                $doc[] = static::object($value);
                $i++;
            } else {
                $doc[] = static::object($key) . ' => ' . static::object($value);
            }
        }

        return '[' . implode(', ', $doc) . ']';
    }

    public static function reflectionType(GuideScope $scope, ReflectionType $type): string
    {
        return match (true) {
            $type instanceof ReflectionNamedType        => ($type->allowsNull() ? 'null|' : '') . $scope->typeHint($type->getName()),
            $type instanceof ReflectionUnionType        => implode('|', array_map(fn($type) => static::reflectionType($scope, $type), $type->getTypes())),
            $type instanceof ReflectionIntersectionType => implode('&', array_map(fn($type) => static::reflectionType($scope, $type), $type->getTypes())),
        };
    }

    public static function reflectionParameter(GuideScope $scope, ReflectionParameter $parameter): string
    {
        $doc = '';

        if ($type = $parameter->getType()) {
            $doc .= static::reflectionType($scope, $type) . ' ';
        }

        $doc .= '$' . $parameter->getName();

        if ($parameter->isDefaultValueAvailable()) {
            $doc .= ' = ' . static::object($parameter->getDefaultValue());
        }

        return $doc;
    }

    public static function reflectionMethodArgs(GuideScope $scope, ReflectionMethod $method): string
    {
        return implode(', ', array_map(static::reflectionParameter(...), $method->getParameters()));
    }

    public static function reflectionMethod(GuideScope $scope, ReflectionMethod $method): string
    {
        $doc = '';

        if ($method->getReturnType()) {
            $doc .= static::reflectionType($scope, $method->getReturnType()) . ' ';
        }

        $doc .= $method->getName() . '(' . static::reflectionMethodArgs($scope, $method) . ')';

        return $doc;
    }

    public static function reflectionProperty(GuideScope $scope, ReflectionProperty $parameter): string
    {
        $doc = '';

        if ($type = $parameter->getType()) {
            $doc .= static::reflectionType($scope, $type) . ' ';
        }

        $doc .= '$' . $parameter->getName();

        return $doc;
    }

}