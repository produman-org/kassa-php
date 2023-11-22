<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Orders\Model;

use ProdumanApi\Traits\ResponseTrait;

class SupplierModel
{
    use ResponseTrait;

    public ?string $counterpartyId = null;

    public ?string $name = null;

    public ?string $inn = null;

    public ?string $phone = null;
}
