<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Cashboxes\Model;

use ProdumanApi\Traits\ResponseTrait;

class AvailableOperationModel
{
    use ResponseTrait;

    public ?string $operationType = null;

    /** @var string[] */
    public array $actionTypes = [];
}
