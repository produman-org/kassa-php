<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Counterparties;

use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Traits\RequestTrait;

class ListRequest implements RequestInterface
{
    use RequestTrait;

    public ?int $limit = null;

    public ?string $cursor = null;

    public ?string $search = null;
}
