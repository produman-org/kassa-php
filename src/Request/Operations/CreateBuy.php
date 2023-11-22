<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsBuyModel;

class CreateBuy extends AbstractCreateRequest
{
    protected string $operationType = 'BUY';

    public ?DetailsBuyModel $details = null;
}
