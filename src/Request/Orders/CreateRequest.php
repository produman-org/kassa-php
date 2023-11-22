<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Orders;

use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Request\Orders\Model\ClientModel;
use ProdumanApi\Request\Orders\Model\DeliveryModel;
use ProdumanApi\Request\Orders\Model\PositionModel;
use ProdumanApi\Traits\RequestTrait;

class CreateRequest implements RequestInterface
{
    use RequestTrait;

    public ?string $createdById = null;

    public ?string $externalId = null;

    /**
     * Potential values are 'OSN', 'USN_INCOME', 'USN_INCOME_OUTCOME', 'ESN', 'PATENT'.
     */
    public ?string $taxationSystem = null;

    public ?ClientModel $client = null;

    public ?DeliveryModel $delivery = null;

    /** @var PositionModel[]|null */
    public ?array $positions = null;
}
