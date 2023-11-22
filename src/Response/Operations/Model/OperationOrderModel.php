<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Operations\Model;

use ProdumanApi\Traits\ResponseTrait;

class OperationOrderModel
{
    use ResponseTrait;

    public ?string $id = null;

    public ?string $externalId = null;

    public ?int $number = null;

    public ?float $orderAmount = null;

    /** @var string[] */
    public array $groups = [];
}
