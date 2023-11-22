<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsSellReturnModel;

class CreateSellReturn extends AbstractCreateRequest
{
    protected string $operationType = 'SELL_RETURN';

    public ?DetailsSellReturnModel $details = null;
}
