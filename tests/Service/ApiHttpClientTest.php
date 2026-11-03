<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Service;

use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Service\ApiHttpClient;
use ProdumanApi\Tests\Mock\MockPsrLogger;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

final class ApiHttpClientTest extends TestCase
{
    private string|false $originalUrl;
    private array $originalEnv;

    protected function setUp(): void
    {
        $this->originalUrl = getenv('PRODUMAN_API_URL');
        $this->originalEnv = $_ENV;
        putenv('PRODUMAN_API_URL');
        unset($_ENV['PRODUMAN_API_URL']);
    }

    protected function tearDown(): void
    {
        putenv(false === $this->originalUrl ? 'PRODUMAN_API_URL' : 'PRODUMAN_API_URL=' . $this->originalUrl);
        $_ENV = $this->originalEnv;
    }

    #[TestWith(['GET', [], '', null, null])]
    #[TestWith(['POST', [], '{"name":"Товар"}', 'token', 'ru'])]
    #[TestWith(['PUT', ['q' => 'hello world'], '{}', null, 'en'])]
    #[TestWith(['PATCH', [], '{}', 'token', null])]
    #[TestWith(['DELETE', [], '', null, null])]
    public function testRequestOptions(string $method, array $query, string $body, ?string $token, ?string $language): void
    {
        $captured = [];
        $http = new MockHttpClient(static function ($requestMethod, $url, $options) use (&$captured) {
            $captured = [$requestMethod, $url, $options];

            return new MockResponse('{"id":"response-id"}');
        });
        $api = new ApiHttpClient($http, new NullLogger(), 'app-id', 'app-secret', 5);
        if (null !== $token) {
            $api->setClientToken($token);
        }
        $api->setLanguage($language);

        self::assertSame('{"id":"response-id"}', $api->request($method, 'orders', $query, $body));
        self::assertSame($method, $captured[0]);
        self::assertStringStartsWith('https://api.produman.org/kassa/v1/orders', $captured[1]);
        if ($query) {
            self::assertStringContainsString('q=hello%20world', $captured[1]);
        }
        $headers = [];
        foreach ($captured[2]['headers'] as $header) {
            [$name, $value] = explode(': ', $header, 2);
            $headers[strtolower($name)] = $value;
        }
        self::assertSame('app-id', $headers['x-app-id']);
        self::assertSame('app-secret', $headers['x-app-secret']);
        self::assertSame($token, $headers['x-client-token'] ?? null);
        self::assertSame($language, $headers['accept-language'] ?? null);
        self::assertSame($body, $captured[2]['body'] ?? '');
        self::assertEquals(5, $captured[2]['timeout']);
        self::assertSame(0, $captured[2]['max_redirects'] ?? null);
        if ('' !== $body) {
            self::assertSame('application/json', $headers['content-type']);
        }
    }

    #[TestWith([null, null, 'https://api.produman.org/kassa'])]
    #[TestWith(['', '', 'https://api.produman.org/kassa'])]
    #[TestWith(['https://process.test/kassa', null, 'https://process.test/kassa'])]
    #[TestWith([null, 'https://env.test/kassa', 'https://env.test/kassa'])]
    #[TestWith(['https://process.test/kassa', 'https://env.test/kassa', 'https://process.test/kassa'])]
    public function testApiUrlPrecedence(?string $process, ?string $env, string $expected): void
    {
        if (null !== $process) {
            putenv('PRODUMAN_API_URL=' . $process);
        }
        if (null !== $env) {
            $_ENV['PRODUMAN_API_URL'] = $env;
        }
        $http = new MockHttpClient(static function ($method, $url) use ($expected) {
            self::assertSame($expected . '/v1/orders', $url);

            return new MockResponse('{}');
        });
        (new ApiHttpClient($http, new NullLogger(), '', '', 1))->request('GET', 'orders');
    }

    #[TestWith([302, HttpException::class])]
    #[TestWith([400, ApiException::class])]
    #[TestWith([401, ApiException::class])]
    #[TestWith([429, ApiException::class])]
    #[TestWith([500, HttpException::class])]
    #[TestWith([503, HttpException::class])]
    public function testMetadataReplacedOnHttpFailure(int $status, string $exceptionClass): void
    {
        $http = new MockHttpClient([
            new MockResponse('{}', ['response_headers' => ['x-ratelimit-remaining: 5', 'x-ratelimit-limit: 10']]),
            new MockResponse('{}', ['http_code' => $status, 'response_headers' => ['x-ratelimit-remaining: 0', 'x-ratelimit-limit: 20']]),
            new MockResponse('{}'),
        ]);
        $api = new ApiHttpClient($http, new NullLogger(), '', '', 1);
        $api->request('GET', 'orders');
        self::assertSame(200, $api->getLastStatusCode());
        self::assertSame('5', $api->getLastXRateLimitRemaining());
        try {
            $api->request('GET', 'orders');
            self::fail('Ожидалось исключение HTTP/API');
        } catch (\RuntimeException $error) {
            self::assertInstanceOf($exceptionClass, $error);
            self::assertInstanceOf(HttpExceptionInterface::class, $error->getPrevious());
        }
        self::assertSame($status, $api->getLastStatusCode());
        self::assertSame('0', $api->getLastXRateLimitRemaining());
        self::assertSame('20', $api->getLastXRateLimitLimit());
        $api->request('GET', 'orders');
        self::assertSame(200, $api->getLastStatusCode());
        self::assertNull($api->getLastXRateLimitRemaining());
        self::assertNull($api->getLastXRateLimitLimit());
    }

    public function testMetadataClearedOnFailureBeforeHeaders(): void
    {
        $calls = 0;
        $cause = new TransportException('Нет соединения');
        $http = new MockHttpClient(static function () use (&$calls, $cause) {
            if (++$calls > 1) {
                throw $cause;
            }

            return new MockResponse('{}', ['response_headers' => ['x-ratelimit-remaining: 5']]);
        });
        $api = new ApiHttpClient($http, new NullLogger(), '', '', 1);
        $api->request('GET', 'orders');
        try {
            $api->request('GET', 'orders');
            self::fail('Ожидалась сетевая ошибка');
        } catch (HttpException $error) {
            self::assertSame($cause, $error->getPrevious());
        }
        self::assertNull($api->getLastStatusCode());
        self::assertNull($api->getLastXRateLimitRemaining());
        self::assertNull($api->getLastXRateLimitLimit());
    }

    public function testFailureWhileReceivingHeaders(): void
    {
        $cause = new TransportException('Ошибка заголовков');
        $api = new ApiHttpClient(new MockHttpClient(new MockResponse('', ['error' => $cause->getMessage()])), new NullLogger(), '', '', 1);
        try {
            $api->request('GET', 'orders');
            self::fail('Ожидалась ошибка при получении ответа');
        } catch (HttpException $error) {
            self::assertInstanceOf(TransportException::class, $error->getPrevious());
        }
        self::assertNull($api->getLastStatusCode());
    }

    public function testMetadataKeptOnFailureReadingBody(): void
    {
        $cause = new TransportException('Ошибка тела');
        $body = (static function () use ($cause) {
            yield '{';
            yield $cause;
        })();
        $response = new MockResponse($body, ['http_code' => 200, 'response_headers' => ['x-ratelimit-remaining: 9']]);
        $api = new ApiHttpClient(new MockHttpClient($response), new NullLogger(), '', '', 1);
        try {
            $api->request('GET', 'orders');
            self::fail('Ожидалась ошибка чтения тела');
        } catch (HttpException $error) {
            self::assertInstanceOf(TransportException::class, $error->getPrevious());
        }
        self::assertSame(200, $api->getLastStatusCode());
        self::assertSame('9', $api->getLastXRateLimitRemaining());
    }

    #[TestWith(['{"message":"Ошибка","code":"VALIDATION_ERROR","details":{"field":"name"}}', true])]
    #[TestWith(['{"message":null,"code":null,"details":null}', true])]
    #[TestWith(['{}', true])]
    #[TestWith(['[]', true])]
    #[TestWith(['[1,2]', true])]
    #[TestWith(['{"message":12}', false])]
    #[TestWith(['{"code":false}', false])]
    #[TestWith(['{"details":"wrong"}', false])]
    #[TestWith(['{broken', false])]
    #[TestWith(['null', false])]
    #[TestWith(['"text"', false])]
    public function testClientErrorContract(string $body, bool $structured): void
    {
        $api = new ApiHttpClient(new MockHttpClient(new MockResponse($body, ['http_code' => 400])), new NullLogger(), '', '', 1);
        try {
            $api->request('GET', 'orders');
            self::fail('Ожидалась ошибка API');
        } catch (\RuntimeException $error) {
            self::assertInstanceOf($structured ? ApiException::class : HttpException::class, $error);
            self::assertInstanceOf(HttpExceptionInterface::class, $error->getPrevious());
            if ($error instanceof ApiException) {
                self::assertSame(400, $error->getStatusCode());
                $decoded = json_decode($body, true);
                self::assertSame($decoded['message'] ?? '', $error->getApiMessage());
                self::assertSame($decoded['code'] ?? '', $error->getApiCode());
                self::assertSame($decoded['details'] ?? [], $error->getApiDetails());
            }
        }
    }

    #[TestWith([200])]
    #[TestWith([302])]
    #[TestWith([400])]
    #[TestWith([500])]
    #[TestWith([0])]
    public function testLogsContainMetadataWithoutPayloadOrSecrets(int $status): void
    {
        $old = ini_set('zend.exception_ignore_args', '0');
        try {
            $logger = new MockPsrLogger();
            $http = new MockHttpClient(static function () use ($status) {
                if (0 === $status) {
                    throw new TransportException('transport-secret');
                }

                return new MockResponse('{"message":"response-secret"}', ['http_code' => $status]);
            });
            $api = new ApiHttpClient($http, $logger, 'app', 'app-secret', 1);
            $api->setClientToken('client-secret');
            try {
                $api->request('POST', 'orders?token=query-secret#fragment-secret', [], '{"secret":"request-secret"}');
            } catch (ApiException|HttpException) {
            }
            $logs = json_encode($logger->getLogs(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
            foreach (['app-secret', 'client-secret', 'request-secret', 'response-secret', 'query-secret', 'fragment-secret', 'transport-secret'] as $secret) {
                self::assertStringNotContainsString($secret, $logs);
            }
            self::assertStringContainsString('/kassa/v1/orders', $logs);
            self::assertNotEmpty($logger->getLogs());
        } finally {
            ini_set('zend.exception_ignore_args', $old);
        }
    }

    public function testClientExceptionRaisedDirectlyByTransportRetainsMetadata(): void
    {
        $response = (new MockHttpClient(new MockResponse('{}', ['http_code' => 400, 'response_headers' => ['x-ratelimit-remaining: 7']])))->request('GET', 'https://fixture.test/orders');
        try {
            $response->getContent();
            self::fail('Ожидалась исходная ошибка HTTP');
        } catch (\Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface $cause) {
            $http = new MockHttpClient(static function () use ($cause) { throw $cause; });
            $api = new ApiHttpClient($http, new NullLogger(), '', '', 1);
            try {
                $api->request('GET', 'orders');
                self::fail('Ожидалась ошибка API');
            } catch (ApiException $error) {
                self::assertSame($cause, $error->getPrevious());
            }
            self::assertSame(400, $api->getLastStatusCode());
            self::assertSame('7', $api->getLastXRateLimitRemaining());
        }
    }
}
