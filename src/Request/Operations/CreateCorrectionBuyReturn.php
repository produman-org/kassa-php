<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsCorrectionModel;

class CreateCorrectionBuyReturn extends AbstractCreateRequest
{
    protected string $operationType = 'CORRECTION_BUY_RETURN';

    public ?DetailsCorrectionModel $details = null;
}
