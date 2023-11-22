<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Traits\RequestTrait;

class DetailsPrintSlipModel
{
    use RequestTrait;

    /** @var SlipItemModel[]|null */
    public ?array $slipItems = null;

    public ?string $orderId = null;
}
