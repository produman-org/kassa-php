<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Mock;

use ProdumanApi\Interfaces\ApiHttpClientInterface;

class MockApiHttpClient implements ApiHttpClientInterface
{
    private string $response;

    private ?string $lastXRateLimitRemaining;

    private ?string $lastXRateLimitLimit;

    private ?int $lastStatusCode;

    private ?string $requestMethod;

    private ?string $requestAction;

    private ?array $requestQuery;

    private ?string $requestBody;

    public function __construct(string $response = '', ?string $lastXRateLimitRemaining = null, ?string $lastXRateLimitLimit = null, ?int $lastStatusCode = null)
    {
        $this->response = $response;
        $this->lastXRateLimitRemaining = $lastXRateLimitRemaining;
        $this->lastXRateLimitLimit = $lastXRateLimitLimit;
        $this->lastStatusCode = $lastStatusCode;
    }

    public function request(string $method, string $action, array $query = [], string $body = ''): string
    {
        $this->requestMethod = $method;
        $this->requestAction = $action;
        $this->requestQuery = $query;
        $this->requestBody = $body;

        return $this->response;
    }

    public function getLastXRateLimitRemaining(): ?string
    {
        return $this->lastXRateLimitRemaining;
    }

    public function getLastXRateLimitLimit(): ?string
    {
        return $this->lastXRateLimitLimit;
    }

    public function getLastStatusCode(): ?int
    {
        return $this->lastStatusCode;
    }

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function getRequestAction(): ?string
    {
        return $this->requestAction;
    }

    public function getRequestQuery(): ?array
    {
        return $this->requestQuery;
    }

    public function getRequestBody(): ?string
    {
        return $this->requestBody;
    }
}
