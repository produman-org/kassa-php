<?php

declare(strict_types=1);

namespace ProdumanApi\Request\Webhooks;

use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Traits\RequestTrait;

class EndpointCreateRequest implements RequestInterface
{
    use RequestTrait;

    /**
     * Potential values are 'ACTIVE', 'INACTIVE'.
     */
    public ?string $status = null;

    public ?string $url = null;

    /**
     * Potential values are 'NO', 'BEARER_TOKEN', 'BASIC_AUTH'.
     */
    public ?string $authType = null;

    public ?string $secret = null;

    /**
     * Potential values are 'OPERATION_COMPLETED', 'OPERATION_FAILED', 'INTEGRATION_TERMINATED', 'INTEGRATION_REQUEST_COMPLETED', 'CASHBOX_DATA_CHANGED'.
     *
     * @var string[]|null
     */
    public ?array $events = null;
}
