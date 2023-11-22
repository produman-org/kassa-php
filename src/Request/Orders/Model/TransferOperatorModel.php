<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Orders\Model;

use ProdumanApi\Traits\RequestTrait;

class TransferOperatorModel
{
    use RequestTrait;

    public ?string $name = null;

    public ?string $inn = null;

    public ?string $phone = null;

    public ?string $address = null;
}
