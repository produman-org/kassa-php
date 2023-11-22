<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Counterparties\Model;

use ProdumanApi\Traits\ResponseTrait;

class AccountModel
{
    use ResponseTrait;

    public ?string $bank = null;

    public ?string $paymentAccount = null;

    public ?string $corrAccount = null;

    public ?string $bic = null;

    public ?bool $default = null;
}
