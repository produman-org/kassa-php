<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Mock;

use ProdumanApi\ClientHelper\AbstractClientHelper;
use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Interfaces\ResponseInterface;

class MockClientHelper extends AbstractClientHelper
{
    public function send(string $method, string $action, ?string $responseType = null, ?RequestInterface $request = null): ?ResponseInterface
    {
        return parent::send($method, $action, $responseType, $request);
    }
}
