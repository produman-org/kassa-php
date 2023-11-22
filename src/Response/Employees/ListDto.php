<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Employees;

use ProdumanApi\Interfaces\ResponseInterface;
use ProdumanApi\Traits\ResponseTrait;

class ListDto implements ResponseInterface
{
    use ResponseTrait {
        ResponseTrait::createObject as createTraitObject;
    }

    /** @var EmployeeDto[] */
    public array $items = [];

    public ?string $nextCursor = null;

    public static function createObject(array $response): self
    {
        $object = self::createTraitObject($response);

        self::fillArrayOfObjects($object, $response, 'items', EmployeeDto::class);

        return $object;
    }
}
