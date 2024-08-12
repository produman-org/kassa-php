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
     * ["a", "aa", "aaa", 1, "en"]
     * ["b", "bb", "bbb", 2, "ru"]
     * ["c", "cc", "ccc", 3, "en"]
     * ["d", "dd", "ddd", 4, "ru"]
     * ["e", "ee", "eee", 5, "en"]
     */
    public function testBuildClient(string $clientToken, string $appId, string $appSecret, int $timeout, string $language): void
    {
        // without logger
        $client = Builder::buildClient($clientToken, $appId, $appSecret, $timeout, null, $language);
        $this->assertInstanceOf(Client::class, $client);

        $apiClient = ReflectionHelper::getProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClient::class, $apiClient);

        $apiClientLogger = ReflectionHelper::getProperty($apiClient, 'logger');
        $this->assertInstanceOf(NullLogger::class, $apiClientLogger);

        // with logger
        $logger = new MockPsrLogger();

        $client = Builder::buildClient($clientToken, $appId, $appSecret, $timeout, $logger, $language);
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
        $this->assertEquals($language, ReflectionHelper::getProperty($apiClient, 'language'));
    }

    /**
     * @testWith
     * ["aa", "aaa", 1, "en"]
     * ["bb", "bbb", 2, "ru"]
     * ["cc", "ccc", 3, "en"]
     * ["dd", "ddd", 4, "ru"]
     * ["ee", "eee", 5, "en"]
     */
    public function testBuildApplicationClient(string $appId, string $appSecret, int $timeout, string $language): void
    {
        // without logger
        $client = Builder::buildApplicationClient($appId, $appSecret, $timeout, null, $language);
        $this->assertInstanceOf(ApplicationClient::class, $client);

        $apiClient = ReflectionHelper::getProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClient::class, $apiClient);

        $apiClientLogger = ReflectionHelper::getProperty($apiClient, 'logger');
        $this->assertInstanceOf(NullLogger::class, $apiClientLogger);

        // with logger
        $logger = new MockPsrLogger();

        $client = Builder::buildApplicationClient($appId, $appSecret, $timeout, $logger, $language);
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
        $this->assertEquals($language, ReflectionHelper::getProperty($apiClient, 'language'));
    }
}
