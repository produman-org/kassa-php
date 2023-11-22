<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Traits\RequestTrait;

class DetailsCashModel
{
    use RequestTrait;

    public ?string $cashMovementCategoryId = null;

    public ?string $comment = null;

    public ?float $amount = null;
}
