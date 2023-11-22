<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsCashModel;

class CreateCashIn extends AbstractCreateRequest
{
    protected string $operationType = 'CASH_IN';

    public ?DetailsCashModel $details = null;
}
