<?php

declare(strict_types=1);

namespace ProdumanApi\Tests;

class ReflectionHelper
{
    public static function getConstants(string $class): array
    {
        $class = new \ReflectionClass($class);

        return $class->getConstants();
    }

    public static function getProperty(object $object, string $property)
    {
        $class = new \ReflectionClass($object);

        $prop = $class->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }

    public static function getParentProperty(object $object, string $property)
    {
        $class = new \ReflectionClass($object);

        $prop = $class->getParentClass()->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($object);
    }
}
