<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Orders\Model;

use ProdumanApi\Traits\RequestTrait;

class ReceivePaymentsOperatorModel
{
    use RequestTrait;

    public ?string $phone = null;
}
