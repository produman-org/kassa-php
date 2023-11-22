<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Traits\RequestTrait;

class DetailsSellReturnModel extends DetailsSellModel
{
    use RequestTrait;

    /** @var ReturnOrderPositionModel[]|null */
    public ?array $returnOrderPositions = null;
}
