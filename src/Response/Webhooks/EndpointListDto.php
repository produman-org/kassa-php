<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Webhooks;

use ProdumanApi\Interfaces\ResponseInterface;
use ProdumanApi\Traits\ResponseTrait;

class EndpointListDto implements ResponseInterface
{
    use ResponseTrait {
        ResponseTrait::createObject as createTraitObject;
    }

    /** @var EndpointDto[] */
    public array $items = [];

    public ?string $nextCursor = null;

    public static function createObject(array $response): self
    {
        $object = self::createTraitObject($response);

        self::fillArrayOfObjects($object, $response, 'items', EndpointDto::class);

        return $object;
    }
}
