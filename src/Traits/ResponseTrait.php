<?php

declare(strict_types=1);

namespace ProdumanApi\Traits;

use ProdumanApi\Interfaces\ResponseInterface;

trait ResponseTrait
{
    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public static function createResponseObject(array $response): ResponseInterface
    {
        $responseObject = self::createObject($response);

        if (!($responseObject instanceof ResponseInterface)) {
            throw new \Exception('Object ' . get_class($responseObject) . ' must be implements ' . ResponseInterface::class);
        }

        return $responseObject;
    }

    /**
     * @throws \ReflectionException
     * @throws \Exception
     */
    public static function createObject(array $response): self
    {
        $object = new self();

        foreach ($object as $fieldName => $fieldValue) {
            if (!isset($response[$fieldName])) {
                continue;
            }

            $fieldValue = $response[$fieldName];

            $rp = new \ReflectionProperty($object, $fieldName);

            $rpType = $rp->getType();

            if (null === $rpType) {
                continue;
            }

            $fieldType = $rpType->getName();

            if ($rpType->isBuiltin()) {
                switch ($fieldType) {
                    case 'int':
                        $fieldValue = intval($fieldValue);
                        break;
                    case 'float':
                        $fieldValue = floatval($fieldValue);
                        break;
                    case 'string':
                        $fieldValue = strval($fieldValue);
                        break;
                    case 'bool':
                        $fieldValue = boolval($fieldValue);
                        break;
                    case 'array':
                        if (!is_array($fieldValue)) {
                            $fieldValue = [$fieldValue];
                        }
                        break;
                    default:
                        continue 2;
                }

                $object->{$fieldName} = $fieldValue;
            } else {
                if (\DateTimeInterface::class === $fieldType) {
                    try {
                        $object->{$fieldName} = new \DateTime($fieldValue);
                    } catch (\Exception $exception) {
                    }

                    continue;
                }

                if (!is_array($fieldValue) || !method_exists($fieldType, 'createObject')) {
                    continue;
                }

                $object->{$fieldName} = $fieldType::createObject($fieldValue);
            }
        }

        return $object;
    }

    public static function fillArrayOfObjects(object $object, array $response, string $field, string $fieldType): void
    {
        if (!isset($response[$field])
            || !is_array($response[$field])
            || !isset($object->{$field})
            || !is_array($object->{$field})) {
            return;
        }

        $object->{$field} = [];

        foreach ($response[$field] as $fieldValue) {
            if (!is_array($fieldValue) || !method_exists($fieldType, 'createObject')) {
                continue;
            }

            $object->{$field}[] = $fieldType::createObject($fieldValue);
        }
    }
}
