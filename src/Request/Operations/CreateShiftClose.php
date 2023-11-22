<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsShiftCloseModel;

class CreateShiftClose extends AbstractCreateRequest
{
    protected string $operationType = 'SHIFT_CLOSE';

    public ?DetailsShiftCloseModel $details = null;
}
