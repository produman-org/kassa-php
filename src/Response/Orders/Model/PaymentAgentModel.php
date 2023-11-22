<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Orders\Model;

use ProdumanApi\Traits\ResponseTrait;

class PaymentAgentModel
{
    use ResponseTrait;

    public ?string $phone = null;

    public ?string $operation = null;
}
