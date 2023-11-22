<?php

declare(strict_types=1);

namespace ProdumanApi\Response\Integrations;

use ProdumanApi\Interfaces\ResponseInterface;
use ProdumanApi\Traits\ResponseTrait;

class IntegrationConnectDto implements ResponseInterface
{
    use ResponseTrait;

    public ?string $requestConnectionId = null;

    public ?string $requestConnectionUrl = null;
}
