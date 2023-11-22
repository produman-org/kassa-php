<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Traits\RequestTrait;

class DetailsCopyReceiptModel
{
    use RequestTrait;

    public ?string $fdNumber = null;

    public ?string $orderId = null;
}
