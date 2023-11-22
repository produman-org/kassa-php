<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Orders\Model;

use ProdumanApi\Traits\RequestTrait;

class PaymentAgentModel
{
    use RequestTrait;

    public ?string $phone = null;

    public ?string $operation = null;
}
