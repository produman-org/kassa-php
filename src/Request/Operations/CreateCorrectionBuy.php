<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsCorrectionModel;

class CreateCorrectionBuy extends AbstractCreateRequest
{
    protected string $operationType = 'CORRECTION_BUY';

    public ?DetailsCorrectionModel $details = null;
}
