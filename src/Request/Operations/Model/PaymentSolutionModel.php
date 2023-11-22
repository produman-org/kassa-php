<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Traits\RequestTrait;

class PaymentSolutionModel
{
    use RequestTrait;

    /**
     * Potential values are '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18'.
     */
    public ?int $id;

    public ?float $amount;

    public ?string $referenceData;
}
