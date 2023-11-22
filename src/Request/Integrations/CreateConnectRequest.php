<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Integrations;

use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Traits\RequestTrait;

class CreateConnectRequest implements RequestInterface
{
    use RequestTrait;

    public ?string $email = null;
}
