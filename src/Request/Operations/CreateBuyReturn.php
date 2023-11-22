<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsBuyReturnModel;

class CreateBuyReturn extends AbstractCreateRequest
{
    protected string $operationType = 'BUY_RETURN';

    public ?DetailsBuyReturnModel $details = null;
}
