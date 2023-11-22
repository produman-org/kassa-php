<?php

declare(strict_types=1);

namespace ProdumanApi\Tests;

use PHPUnit\Framework\TestCase;
use ProdumanApi\Builder;
use ProdumanApi\Client\ApplicationClient;
use ProdumanApi\Client\Client;
use ProdumanApi\Service\ApiHttpClient;
use ProdumanApi\Tests\Mock\MockPsrLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\CurlHttpClient;

final class BuilderTest extends TestCase
{
    /**
     * @testWith
     * ["a", "aa", "aaa", 1]
     * ["b", "bb", "bbb", 2]
     * ["c", "cc", "ccc", 3]
     * ["d", "dd", "ddd", 4]
     * ["e", "ee", "eee", 5]
     */
    public function testBuildClient(string $clientToken, string $appId, string $appSecret, int $timeout): void
    {
        // without logger
        $client = Builder::buildClient($clientToken, $appId, $appSecret, $timeout);
        $this->assertInstanceOf(Client::class, $client);

        $apiClient = ReflectionHelper::getProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClient::class, $apiClient);

        $apiClientLogger = ReflectionHelper::getProperty($apiClient, 'logger');
        $this->assertInstanceOf(NullLogger::class, $apiClientLogger);

        // with logger
        $logger = new MockPsrLogger();

        $client = Builder::buildClient($clientToken, $appId, $appSecret, $timeout, $logger);
        $this->assertInstanceOf(Client::class, $client);

        $apiClient = ReflectionHelper::getProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClient::class, $apiClient);

        $apiClientHttpClient = ReflectionHelper::getProperty($apiClient, 'httpClient');
        $this->assertInstanceOf(CurlHttpClient::class, $apiClientHttpClient);

        $apiClientLogger = ReflectionHelper::getProperty($apiClient, 'logger');
        $this->assertInstanceOf(LoggerInterface::class, $apiClientLogger);
        $this->assertSame($logger, $apiClientLogger);

        $this->assertEquals($clientToken, ReflectionHelper::getProperty($apiClient, 'clientToken'));
        $this->assertEquals($appId, ReflectionHelper::getProperty($apiClient, 'appId'));
        $this->assertEquals($appSecret, ReflectionHelper::getProperty($apiClient, 'appSecret'));
        $this->assertEquals($timeout, ReflectionHelper::getProperty($apiClient, 'timeout'));
    }

    /**
     * @testWith
     * ["aa", "aaa", 1]
     * ["bb", "bbb", 2]
     * ["cc", "ccc", 3]
     * ["dd", "ddd", 4]
     * ["ee", "eee", 5]
     */
    public function testBuildApplicationClient(string $appId, string $appSecret, int $timeout): void
    {
        // without logger
        $client = Builder::buildApplicationClient($appId, $appSecret, $timeout);
        $this->assertInstanceOf(ApplicationClient::class, $client);

        $apiClient = ReflectionHelper::getProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClient::class, $apiClient);

        $apiClientLogger = ReflectionHelper::getProperty($apiClient, 'logger');
        $this->assertInstanceOf(NullLogger::class, $apiClientLogger);

        // with logger
        $logger = new MockPsrLogger();

        $client = Builder::buildApplicationClient($appId, $appSecret, $timeout, $logger);
        $this->assertInstanceOf(ApplicationClient::class, $client);

        $apiClient = ReflectionHelper::getProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClient::class, $apiClient);

        $apiClientLogger = ReflectionHelper::getProperty($apiClient, 'logger');
        $this->assertInstanceOf(LoggerInterface::class, $apiClientLogger);
        $this->assertSame($logger, $apiClientLogger);

        $apiClientHttpClient = ReflectionHelper::getProperty($apiClient, 'httpClient');
        $this->assertInstanceOf(CurlHttpClient::class, $apiClientHttpClient);

        $this->assertEquals($appId, ReflectionHelper::getProperty($apiClient, 'appId'));
        $this->assertEquals($appSecret, ReflectionHelper::getProperty($apiClient, 'appSecret'));
        $this->assertEquals($timeout, ReflectionHelper::getProperty($apiClient, 'timeout'));
    }
}
