<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Cashboxes\Model;

use ProdumanApi\Traits\ResponseTrait;

class AvailablePaymentSolutionModel
{
    use ResponseTrait;

    public ?int $id = null;

    public ?string $name = null;

    public ?bool $availableInInterface = null;

    public ?bool $integrated = null;

    public ?string $fiscalType = null;

    public ?bool $original = null;
}
