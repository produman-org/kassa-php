<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Operations\Model;

use ProdumanApi\Traits\ResponseTrait;

class FiscalAmountModel
{
    use ResponseTrait;

    public ?string $type = null;

    public ?float $amount = null;
}
