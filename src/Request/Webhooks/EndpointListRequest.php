<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Webhooks;

use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Traits\RequestTrait;

class EndpointListRequest implements RequestInterface
{
    use RequestTrait;

    public ?int $limit = null;

    public ?string $cursor = null;

    /**
     * Potential values are 'ACTIVE', 'INACTIVE'.
     */
    public ?string $status = null;
}
