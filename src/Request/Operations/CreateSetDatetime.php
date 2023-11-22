<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations;

use ProdumanApi\Request\Operations\Model\DetailsSetDatetimeModel;

class CreateSetDatetime extends AbstractCreateRequest
{
    protected string $operationType = 'SET_DATETIME';

    public ?DetailsSetDatetimeModel $details = null;
}
