<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Mock\Request;

use ProdumanApi\Interfaces\RequestInterface;

class MockRequest implements RequestInterface
{
    private array $requestContent;

    public function __construct(array $requestContent = [])
    {
        $this->requestContent = $requestContent;
    }

    public function getRequestContent(): array
    {
        return $this->requestContent;
    }
}
