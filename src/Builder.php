<?php

declare(strict_types=1);

namespace ProdumanApi;

use ProdumanApi\Client\ApplicationClient;
use ProdumanApi\Client\Client;
use ProdumanApi\Service\ApiHttpClient;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\CurlHttpClient;

/**
 * @see Tests\BuilderTest
 */
class Builder
{
    public static function buildClient(string $clientToken, string $appId, string $appSecret, int $timeout = 5, ?LoggerInterface $logger = null): Client
    {
        if (null === $logger) {
            $logger = new NullLogger();
        }

        $apiClient = new ApiHttpClient(new CurlHttpClient(), $logger, $appId, $appSecret, $timeout);

        $apiClient->setClientToken($clientToken);

        return new Client($apiClient);
    }

    public static function buildApplicationClient(string $appId, string $appSecret, int $timeout = 5, ?LoggerInterface $logger = null): ApplicationClient
    {
        if (null === $logger) {
            $logger = new NullLogger();
        }

        $apiClient = new ApiHttpClient(new CurlHttpClient(), $logger, $appId, $appSecret, $timeout);

        return new ApplicationClient($apiClient);
    }
}
