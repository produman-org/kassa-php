<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Orders\Model;

use ProdumanApi\Traits\ResponseTrait;

class TransferOperatorModel
{
    use ResponseTrait;

    public ?string $name = null;

    public ?string $inn = null;

    public ?string $phone = null;

    public ?string $address = null;
}
