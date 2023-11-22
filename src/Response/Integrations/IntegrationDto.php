<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Integrations;

use ProdumanApi\Interfaces\ResponseInterface;
use ProdumanApi\Response\Integrations\Model\AccessModel;
use ProdumanApi\Traits\ResponseTrait;

class IntegrationDto implements ResponseInterface
{
    use ResponseTrait {
        ResponseTrait::createObject as createTraitObject;
    }

    public ?string $id = null;

    public ?string $clientToken = null;

    /** @var AccessModel[] */
    public array $access = [];

    public static function createObject(array $response): self
    {
        $object = self::createTraitObject($response);

        self::fillArrayOfObjects($object, $response, 'access', AccessModel::class);

        return $object;
    }
}
