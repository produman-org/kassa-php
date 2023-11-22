<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Client;

use PHPUnit\Framework\TestCase;
use ProdumanApi\Client\ApplicationClient;
use ProdumanApi\Client\Client;
use ProdumanApi\ClientHelper\Cashboxes;
use ProdumanApi\ClientHelper\CashMovementCategories;
use ProdumanApi\ClientHelper\Counterparties;
use ProdumanApi\ClientHelper\Employees;
use ProdumanApi\ClientHelper\Integrations;
use ProdumanApi\ClientHelper\Operations;
use ProdumanApi\ClientHelper\Orders;
use ProdumanApi\ClientHelper\Webhooks;
use ProdumanApi\Interfaces\ApiHttpClientInterface;
use ProdumanApi\Tests\Mock\MockApiHttpClient;
use ProdumanApi\Tests\ReflectionHelper;

class ClientTest extends TestCase
{
    /**
     * @testWith
     * ["1", "11", 1]
     * ["2", "22", 2]
     * ["3", "33", 3]
     * ["4", "44", 4]
     * ["5", "55", 5]
     */
    public function testClient(string $remaining, string $limit, int $status): void
    {
        $mockApiHttpClient = new MockApiHttpClient('', $remaining, $limit, $status);

        $client = new Client($mockApiHttpClient);

        $apiHttpClient = ReflectionHelper::getParentProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClientInterface::class, $apiHttpClient);
        $this->assertSame($mockApiHttpClient, $apiHttpClient);

        $this->assertInstanceOf(Cashboxes::class, ReflectionHelper::getProperty($client, 'cashboxes'));
        $this->assertInstanceOf(CashMovementCategories::class, ReflectionHelper::getProperty($client, 'cashMovementCategories'));
        $this->assertInstanceOf(Counterparties::class, ReflectionHelper::getProperty($client, 'counterparties'));
        $this->assertInstanceOf(Employees::class, ReflectionHelper::getProperty($client, 'employees'));
        $this->assertInstanceOf(Operations::class, ReflectionHelper::getProperty($client, 'operations'));
        $this->assertInstanceOf(Orders::class, ReflectionHelper::getProperty($client, 'orders'));

        $this->assertEquals($remaining, $client->getLastXRateLimitRemaining());
        $this->assertEquals($limit, $client->getLastXRateLimitLimit());
        $this->assertEquals($status, $client->getLastStatusCode());
    }

    /**
     * @testWith
     * ["1", "11", 1]
     * ["2", "22", 2]
     * ["3", "33", 3]
     * ["4", "44", 4]
     * ["5", "55", 5]
     */
    public function testApplicationClient(string $remaining, string $limit, int $status): void
    {
        $mockApiHttpClient = new MockApiHttpClient('', $remaining, $limit, $status);

        $client = new ApplicationClient($mockApiHttpClient);

        $apiHttpClient = ReflectionHelper::getParentProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClientInterface::class, $apiHttpClient);
        $this->assertSame($mockApiHttpClient, $apiHttpClient);

        $this->assertInstanceOf(Integrations::class, ReflectionHelper::getProperty($client, 'integrations'));
        $this->assertInstanceOf(Webhooks::class, ReflectionHelper::getProperty($client, 'webhooks'));

        $this->assertEquals($remaining, $client->getLastXRateLimitRemaining());
        $this->assertEquals($limit, $client->getLastXRateLimitLimit());
        $this->assertEquals($status, $client->getLastStatusCode());
    }
}
