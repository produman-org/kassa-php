<?php

declare(strict_types=1);

namespace ProdumanApi\Request\CashMovementCategories;

use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Traits\RequestTrait;

class ListRequest implements RequestInterface
{
    use RequestTrait;

    public ?int $limit = null;

    public ?string $cursor = null;

    /**
     * Potential values are 'IN', 'OUT'.
     */
    public ?string $type = null;
}
