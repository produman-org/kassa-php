<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsCopyReceiptModel;

class CreateCopyReceipt extends AbstractCreateRequest
{
    protected string $operationType = 'COPY_RECEIPT';

    public ?DetailsCopyReceiptModel $details = null;
}
