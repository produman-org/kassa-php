<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Traits\RequestTrait;

class CashEditRequest implements RequestInterface
{
    use RequestTrait;

    public ?string $cashMovementCategoryId = null;

    public ?string $comment = null;
}
