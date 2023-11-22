<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Webhooks;

use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Traits\RequestTrait;

class EndpointTestRequest implements RequestInterface
{
    use RequestTrait;

    /**
     * Potential values are 'OPERATION_COMPLETED', 'OPERATION_FAILED', 'INTEGRATION_TERMINATED', 'INTEGRATION_REQUEST_COMPLETED', 'CASHBOX_DATA_CHANGED'.
     */
    public ?string $event = null;

    public ?bool $skipVerify = false;
}
