<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsPrintSlipModel;

class CreatePrintSlip extends AbstractCreateRequest
{
    protected string $operationType = 'PRINT_SLIP';

    public ?DetailsPrintSlipModel $details = null;
}
