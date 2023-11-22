<?php

declare(strict_types=1);

namespace ProdumanApi\Client;

use ProdumanApi\Interfaces\ApiHttpClientInterface;

/**
 * @see \ProdumanApi\Tests\Client\ClientTest
 */
abstract class AbstractClient
{
    protected ApiHttpClientInterface $apiClient;

    public function __construct(ApiHttpClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getLastXRateLimitRemaining(): ?string
    {
        return $this->apiClient->getLastXRateLimitRemaining();
    }

    public function getLastXRateLimitLimit(): ?string
    {
        return $this->apiClient->getLastXRateLimitLimit();
    }

    public function getLastStatusCode(): ?int
    {
        return $this->apiClient->getLastStatusCode();
    }
}
