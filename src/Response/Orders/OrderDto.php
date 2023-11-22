<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Orders;

use ProdumanApi\Interfaces\ResponseInterface;
use ProdumanApi\Response\Orders\Model\ClientModel;
use ProdumanApi\Response\Orders\Model\DeliveryModel;
use ProdumanApi\Response\Orders\Model\OrderPositionModel;
use ProdumanApi\Traits\ResponseTrait;

class OrderDto implements ResponseInterface
{
    use ResponseTrait {
        ResponseTrait::createObject as createTraitObject;
    }

    public ?string $id = null;

    public ?string $createdById = null;

    public ?string $externalId = null;

    public ?\DateTimeInterface $createdAt = null;

    public ?int $number = null;

    public ?float $totalAmount = null;

    public ?string $taxationSystem = null;

    public ?ClientModel $client = null;

    public ?DeliveryModel $delivery = null;

    /** @var OrderPositionModel[] */
    public array $positions = [];

    public static function createObject(array $response): self
    {
        $object = self::createTraitObject($response);

        self::fillArrayOfObjects($object, $response, 'positions', OrderPositionModel::class);

        return $object;
    }
}
