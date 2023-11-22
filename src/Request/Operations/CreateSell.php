<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsSellModel;

class CreateSell extends AbstractCreateRequest
{
    protected string $operationType = 'SELL';

    public ?DetailsSellModel $details = null;
}
