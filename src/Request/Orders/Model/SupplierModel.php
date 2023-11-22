<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Orders\Model;

use ProdumanApi\Traits\RequestTrait;

class SupplierModel
{
    use RequestTrait;

    public ?string $counterpartyId = null;

    public ?string $name = null;

    public ?string $inn = null;

    public ?string $phone = null;
}
