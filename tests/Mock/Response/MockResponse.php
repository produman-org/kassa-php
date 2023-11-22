<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Mock\Response;

use ProdumanApi\Interfaces\ResponseInterface;

class MockResponse implements ResponseInterface
{
    private array $response;

    public static function createResponseObject(array $response): ResponseInterface
    {
        $responseObject = new self();

        $responseObject->response = $response;

        return $responseObject;
    }

    public function getResponse(): array
    {
        return $this->response;
    }
}
