<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\ClientHelper;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ProdumanApi\Exception\JsonRequestException;
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
    #[TestWith(['GET', 'aaa'])]
    #[TestWith(['POST', 'bbb'])]
    #[TestWith(['PUT', 'ccc'])]
    #[TestWith(['PATCH', 'ddd'])]
    #[TestWith(['DELETE', 'eee'])]
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

    #[TestWith(['GET', ['aaa' => 'aaa']])]
    #[TestWith(['POST', ['bbb' => 'bbb']])]
    #[TestWith(['PUT', ['ccc' => 'ccc']])]
    #[TestWith(['PATCH', ['ddd' => 'ddd']])]
    #[TestWith(['DELETE', ['eee' => 'eee']])]
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

    #[TestWith(['{"aaa":"aaa"}'])]
    #[TestWith(['{"bbb":"bbb"}'])]
    #[TestWith(['{"ccc":"ccc"}'])]
    #[TestWith(['{"ddd":"ddd"}'])]
    #[TestWith(['{"eee":"eee"}'])]
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

    #[TestWith(['{aaa'])]
    #[TestWith(['bbb}'])]
    #[TestWith(['{"ccc}'])]
    #[TestWith(['{ddd"}'])]
    #[TestWith(['eee'])]
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

    #[TestWith(['null'])]
    #[TestWith(['true'])]
    #[TestWith(['123'])]
    #[TestWith(['"text"'])]
    public function testScalarResponseIsAnSdkFormatError(string $body): void
    {
        $helper = new MockClientHelper(new MockApiHttpClient($body));
        $this->expectException(JsonResponseException::class);
        $helper->send('GET', 'orders', MockResponse::class);
    }

    #[TestWith(['{broken'])]
    #[TestWith([''])]
    public function testJsonSyntaxErrorRetainsCause(string $body): void
    {
        $helper = new MockClientHelper(new MockApiHttpClient($body));
        try {
            $helper->send('GET', 'orders', MockResponse::class);
            self::fail('Ожидалась ошибка JSON');
        } catch (JsonResponseException $error) {
            self::assertInstanceOf(\JsonException::class, $error->getPrevious());
        }
    }

    #[TestWith(['{}', []])]
    #[TestWith(['[]', []])]
    #[TestWith(['[1,2]', [1, 2]])]
    public function testAcceptedArrayRootsRemainSupported(string $body, array $expected): void
    {
        $response = (new MockClientHelper(new MockApiHttpClient($body)))->send('GET', 'orders', MockResponse::class);
        self::assertSame($expected, $response->getResponse());
    }

    #[TestWith(["\xB1"])]
    #[TestWith([INF])]
    #[TestWith([NAN])]
    public function testEncodingFailureOccursBeforeTransport(mixed $value): void
    {
        $transport = $this->createMock(ApiHttpClientInterface::class);
        $transport->expects($this->never())->method('request');
        try {
            (new MockClientHelper($transport))->send('POST', 'orders', null, new MockRequest(['value' => $value]));
            self::fail('Ожидалась ошибка кодирования запроса');
        } catch (JsonRequestException $error) {
            self::assertInstanceOf(\JsonException::class, $error->getPrevious());
        }
    }

    public function testUserRequestExceptionIsNotWrapped(): void
    {
        $cause = new \LogicException('Ошибка вызывающего кода');
        $request = new class($cause) implements \ProdumanApi\Interfaces\RequestInterface {
            public function __construct(private \LogicException $cause)
            {
            }

            public function getRequestContent(): array
            {
                throw $this->cause;
            }
        };
        $transport = $this->createMock(ApiHttpClientInterface::class);
        $transport->expects($this->never())->method('request');
        try {
            (new MockClientHelper($transport))->send('POST', 'orders', null, $request);
            self::fail('Ожидалась исходная ошибка');
        } catch (\LogicException $error) {
            self::assertSame($cause, $error);
        }
    }

    #[TestWith([''])]
    #[TestWith(['not JSON'])]
    public function testResponseWithoutDtoDoesNotNeedJson(string $body): void
    {
        self::assertNull((new MockClientHelper(new MockApiHttpClient($body)))->send('DELETE', 'orders/1'));
    }
}
