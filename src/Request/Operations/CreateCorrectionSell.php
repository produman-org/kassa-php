<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsCorrectionModel;

class CreateCorrectionSell extends AbstractCreateRequest
{
    protected string $operationType = 'CORRECTION_SELL';

    public ?DetailsCorrectionModel $details = null;
}
