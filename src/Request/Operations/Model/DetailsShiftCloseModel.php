<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Traits\RequestTrait;

class DetailsShiftCloseModel
{
    use RequestTrait;

    public ?bool $onlyCashbox = null;
}
