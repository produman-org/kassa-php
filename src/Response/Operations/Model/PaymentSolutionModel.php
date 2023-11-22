<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Operations\Model;

use ProdumanApi\Traits\ResponseTrait;

class PaymentSolutionModel
{
    use ResponseTrait;

    public ?int $id = null;

    public ?float $amount = null;

    public ?PaymentSolutionDetailsModel $details = null;
}
