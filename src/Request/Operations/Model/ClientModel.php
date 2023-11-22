<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Operations\Model;

use ProdumanApi\Traits\RequestTrait;

class ClientModel
{
    use RequestTrait;

    public ?string $name = null;

    public ?string $inn = null;
}
