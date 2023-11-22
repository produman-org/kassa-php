<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Traits\RequestTrait;

class ReturnOrderPositionModel
{
    use RequestTrait;

    public ?string $positionId = null;

    public ?float $quantity = null;

    public ?float $price = null;

    /** @var string[]|null */
    public ?array $marks = null;
}
