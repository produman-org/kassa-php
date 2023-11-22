<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

class CreateShiftOpen extends AbstractCreateRequest
{
    protected string $operationType = 'SHIFT_OPEN';
}
