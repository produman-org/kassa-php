<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsCorrectionModel;

class CreateCorrectionSellReturn extends AbstractCreateRequest
{
    protected string $operationType = 'CORRECTION_SELL_RETURN';

    public ?DetailsCorrectionModel $details = null;
}
