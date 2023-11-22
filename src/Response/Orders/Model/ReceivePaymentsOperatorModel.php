<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Orders\Model;

use ProdumanApi\Traits\ResponseTrait;

class ReceivePaymentsOperatorModel
{
    use ResponseTrait;

    public ?string $phone = null;
}
