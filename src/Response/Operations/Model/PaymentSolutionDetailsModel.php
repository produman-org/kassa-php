<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Operations\Model;

use ProdumanApi\Traits\ResponseTrait;

class PaymentSolutionDetailsModel
{
    use ResponseTrait;

    public ?string $slip = null;

    public ?string $referenceData = null;
}
