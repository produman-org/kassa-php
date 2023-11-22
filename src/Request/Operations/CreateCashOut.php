<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

class CreateCashOut extends CreateCashIn
{
    protected string $operationType = 'CASH_OUT';
}
