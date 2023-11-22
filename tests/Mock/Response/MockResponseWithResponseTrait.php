<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Mock\Response;

use ProdumanApi\Interfaces\ResponseInterface;
use ProdumanApi\Tests\Mock\Response\Model\ArrayElement;
use ProdumanApi\Traits\ResponseTrait;

class MockResponseWithResponseTrait implements ResponseInterface
{
    use ResponseTrait {
        ResponseTrait::createObject as createTraitObject;
    }

    public ?string $stringField = null;

    public ?int $intField = null;

    public ?float $floatField = null;

    public ?bool $boolField = null;

    public ?\DateTimeInterface $dateField = null;

    /** @var ArrayElement[]|null */
    public ?array $arrayField = null;

    public static function createObject(array $response): self
    {
        $object = self::createTraitObject($response);

        self::fillArrayOfObjects($object, $response, 'arrayField', ArrayElement::class);

        return $object;
    }
}
