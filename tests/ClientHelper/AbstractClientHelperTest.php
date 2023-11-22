<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\ClientHelper;

use PHPUnit\Framework\TestCase;
use ProdumanApi\Exception\JsonResponseException;
use ProdumanApi\Interfaces\ApiHttpClientInterface;
use ProdumanApi\Interfaces\ResponseInterface;
use ProdumanApi\Tests\Mock\MockApiHttpClient;
use ProdumanApi\Tests\Mock\MockClientHelper;
use ProdumanApi\Tests\Mock\Request\MockRequest;
use ProdumanApi\Tests\Mock\Response\MockResponse;
use ProdumanApi\Tests\ReflectionHelper;

class AbstractClientHelperTest extends TestCase
{
    /**
     * @testWith
     *  ["GET", "aaa"]
     *  ["POST", "bbb"]
     *  ["PUT", "ccc"]
     *  ["PATCH", "ddd"]
     *  ["DELETE", "eee"]
     */
    public function testSendEmpty(string $method, string $action): void
    {
        $mockApiHttpClient = new MockApiHttpClient();

        $client = new MockClientHelper($mockApiHttpClient);

        $apiHttpClient = ReflectionHelper::getParentProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClientInterface::class, $apiHttpClient);
        $this->assertSame($mockApiHttpClient, $apiHttpClient);

        $response = $client->send($method, $action);

        $this->assertNull($response);
        $this->assertEquals($method, $mockApiHttpClient->getRequestMethod());
        $this->assertEquals($action, $mockApiHttpClient->getRequestAction());
        $this->assertEquals([], $mockApiHttpClient->getRequestQuery());
        $this->assertEquals('', $mockApiHttpClient->getRequestBody());
    }

    /**
     * @testWith
     *  ["GET", {"aaa":"aaa"}]
     *  ["POST", {"bbb":"bbb"}]
     *  ["PUT", {"ccc":"ccc"}]
     *  ["PATCH", {"ddd":"ddd"}]
     *  ["DELETE", {"eee":"eee"}]
     */
    public function testRequestSend(string $method, array $requestContent): void
    {
        $mockApiHttpClient = new MockApiHttpClient();

        $client = new MockClientHelper($mockApiHttpClient);

        $apiHttpClient = ReflectionHelper::getParentProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClientInterface::class, $apiHttpClient);
        $this->assertSame($mockApiHttpClient, $apiHttpClient);

        $request = new MockRequest($requestContent);

        $client->send($method, 'test', null, $request);

        if ('GET' === $method) {
            $this->assertEquals($requestContent, $mockApiHttpClient->getRequestQuery());
            $this->assertEquals('', $mockApiHttpClient->getRequestBody());
        } else {
            $this->assertEquals([], $mockApiHttpClient->getRequestQuery());
            $this->assertEquals(json_encode($requestContent), $mockApiHttpClient->getRequestBody());
        }
    }

    /**
     * @testWith
     *  ["{\"aaa\":\"aaa\"}"]
     *  ["{\"bbb\":\"bbb\"}"]
     *  ["{\"ccc\":\"ccc\"}"]
     *  ["{\"ddd\":\"ddd\"}"]
     *  ["{\"eee\":\"eee\"}"]
     */
    public function testResponseSend(string $jsonResponse): void
    {
        $mockApiHttpClient = new MockApiHttpClient($jsonResponse);

        $client = new MockClientHelper($mockApiHttpClient);

        $apiHttpClient = ReflectionHelper::getParentProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClientInterface::class, $apiHttpClient);
        $this->assertSame($mockApiHttpClient, $apiHttpClient);

        /** @var MockResponse $response */
        $response = $client->send('GET', 'test', MockResponse::class);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(json_decode($jsonResponse, true), $response->getResponse());
    }

    public function testSendWithBadResponseType(): void
    {
        $mockApiHttpClient = new MockApiHttpClient('{"test":"test"}');

        $client = new MockClientHelper($mockApiHttpClient);

        $apiHttpClient = ReflectionHelper::getParentProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClientInterface::class, $apiHttpClient);
        $this->assertSame($mockApiHttpClient, $apiHttpClient);

        $this->expectException(\Exception::class);

        $client->send('GET', 'test', \stdClass::class);
    }

    /**
     * @testWith
     *  ["{aaa"]
     *  ["bbb}"]
     *  ["{\"ccc}"]
     *  ["{ddd\"}"]
     *  ["eee"]
     */
    public function testSendWithBadJsonResponse(string $jsonResponse): void
    {
        $mockApiHttpClient = new MockApiHttpClient($jsonResponse);

        $client = new MockClientHelper($mockApiHttpClient);

        $apiHttpClient = ReflectionHelper::getParentProperty($client, 'apiClient');
        $this->assertInstanceOf(ApiHttpClientInterface::class, $apiHttpClient);
        $this->assertSame($mockApiHttpClient, $apiHttpClient);

        $this->expectException(JsonResponseException::class);

        $client->send('GET', 'test', MockResponse::class);
    }
}
