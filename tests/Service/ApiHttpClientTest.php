<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Service;

use PHPUnit\Framework\TestCase;
use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Service\ApiHttpClient;
use ProdumanApi\Tests\Mock\MockPsrLogger;
use ProdumanApi\Tests\Mock\MockSymfonyClientException;
use ProdumanApi\Tests\Mock\MockSymfonyHttpClient;
use ProdumanApi\Tests\Mock\MockSymfonyHttpException;
use ProdumanApi\Tests\Mock\MockSymfonyResponse;
use ProdumanApi\Tests\ReflectionHelper;
use Psr\Log\NullLogger;

final class ApiHttpClientTest extends TestCase
{
    /**
     * @testWith
     * ["a"]
     * ["b"]
     * ["c"]
     * ["d"]
     * ["e"]
     */
    public function testSetClientToken(string $clientToken): void
    {
        $symfonyHttpClient = new MockSymfonyHttpClient();

        $apiHttpClient = new ApiHttpClient($symfonyHttpClient, new NullLogger(), '', '', 1);

        // without token
        $apiHttpClient->request('GET', '');
        $requestOptions = $symfonyHttpClient->getRequestOptions();

        $this->assertContains('headers', array_keys($requestOptions));
        $this->assertNotContains('X-CLIENT-TOKEN', array_keys($requestOptions['headers']));

        // with token
        $apiHttpClient->setClientToken($clientToken);
        $apiHttpClient->request('GET', '');
        $requestOptions = $symfonyHttpClient->getRequestOptions();

        $this->assertContains('headers', array_keys($requestOptions));
        $this->assertContains('X-CLIENT-TOKEN', array_keys($requestOptions['headers']));
        $this->assertEquals($clientToken, $requestOptions['headers']['X-CLIENT-TOKEN']);
    }

    /**
     * @testWith
     * ["101", "102", 100]
     * ["201", "202", 200]
     * ["301", "302", 300]
     * ["401", "402", 400]
     * ["501", "502", 500]
     */
    public function testLastVariables(string $lastXRateLimitRemaining, string $lastXRateLimitLimit, int $lastStatusCode): void
    {
        $symfonyResponse = new MockSymfonyResponse([
            'x-ratelimit-remaining' => [$lastXRateLimitRemaining],
            'x-ratelimit-limit' => [$lastXRateLimitLimit],
        ], [], $lastStatusCode);

        $symfonyHttpClient = new MockSymfonyHttpClient($symfonyResponse);

        $apiHttpClient = new ApiHttpClient($symfonyHttpClient, new NullLogger(), '', '', 1);

        $apiHttpClient->request('GET', '');

        $this->assertEquals($lastXRateLimitRemaining, $apiHttpClient->getLastXRateLimitRemaining());
        $this->assertEquals($lastXRateLimitLimit, $apiHttpClient->getLastXRateLimitLimit());
        $this->assertEquals($lastStatusCode, $apiHttpClient->getLastStatusCode());
    }

    /**
     * @testWith
     * ["aaa", "https://a.a"]
     * ["bbb"]
     * ["ccc", "https://c.c"]
     * ["ddd"]
     * ["eee", "https://e.e"]
     */
    public function testEnvUri(string $action, ?string $otherUrl = null): void
    {
        $constants = ReflectionHelper::getConstants(ApiHttpClient::class);

        $this->assertContains('API_URL', array_keys($constants));
        $this->assertContains('API_VERSION', array_keys($constants));

        $apiUrl = $constants['API_URL'];
        $apiVersion = $constants['API_VERSION'];

        if (null !== $otherUrl) {
            putenv('PRODUMAN_API_URL=' . $otherUrl);

            $apiUrl = $otherUrl;
        }

        $symfonyHttpClient = new MockSymfonyHttpClient();

        $apiHttpClient = new ApiHttpClient($symfonyHttpClient, new NullLogger(), '', '', 1);

        $uri = sprintf('%s/%s/%s', $apiUrl, $apiVersion, $action);

        $apiHttpClient->request('GET', $action);

        $this->assertEquals($uri, $symfonyHttpClient->getRequestUrl());

        putenv('PRODUMAN_API_URL=' . null);
    }

    /**
     * @testWith
     * ["aaa", 200, "aaa", "NOT_FOUND", ["a", "aa"]]
     * ["aaa", 200, null, "NOT_FOUND", ["a", "aa"]]
     * ["aaa", 200, "aaa", null, ["a", "aa"]]
     * ["aaa", 200, null, "NOT_FOUND", []]
     * ["aaa", 200, "aaa", null, null]
     * ["aaa", 200, "aaa", "NOT_FOUND", null]
     * ["aaa", 200, null, "NOT_FOUND", ["a", "aa"]]
     * ["aaa", 200, null, null, ["a", "aa"]]
     * ["aaa", 200, null, null, null]
     */
    public function testRequestGoodApiException(string $message, int $code, $apiMessage, $apiCode, $apiDetails): void
    {
        $symfonyHttpExceptionResponse = new MockSymfonyResponse([], array_filter([
            'message' => $apiMessage,
            'code' => $apiCode,
            'details' => $apiDetails,
        ]));

        $symfonyHttpClient = new MockSymfonyHttpClient(null, new MockSymfonyClientException($symfonyHttpExceptionResponse, $message, $code));

        $apiHttpClient = new ApiHttpClient($symfonyHttpClient, new NullLogger(), '', '', 1);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($code);

        $apiHttpClient->request('GET', '');
    }

    /**
     * @testWith
     * ["aaa", 200, ["aaa"], "NOT_FOUND", ["a", "aa"]]
     * ["aaa", 200, "aaa", ["NOT_FOUND"], ["a", "aa"]]
     * ["aaa", 200, "aaa", "NOT_FOUND", "aaa"]
     * ["aaa", 200, 123, "NOT_FOUND", ["a", "aa"]]
     * ["aaa", 200, "aaa", 123, ["a", "aa"]]
     * ["aaa", 200, "aaa", "NOT_FOUND", 123]
     */
    public function testRequestBadApiException(string $message, int $code, $apiMessage, $apiCode, $apiDetails): void
    {
        $symfonyHttpExceptionResponse = new MockSymfonyResponse([], array_filter([
            'message' => $apiMessage,
            'code' => $apiCode,
            'details' => $apiDetails,
        ]));

        $symfonyHttpClient = new MockSymfonyHttpClient(null, new MockSymfonyClientException($symfonyHttpExceptionResponse, $message, $code));

        $apiHttpClient = new ApiHttpClient($symfonyHttpClient, new NullLogger(), '', '', 1);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($code);

        $apiHttpClient->request('GET', '');
    }

    /**
     * @testWith
     * ["aaa", 100]
     * ["bbb", 200]
     * ["ccc", 300]
     * ["ddd", 400]
     * ["eee", 500]
     */
    public function testRequestHttpException(string $message, int $code): void
    {
        $symfonyHttpClient = new MockSymfonyHttpClient(null, new MockSymfonyHttpException(new MockSymfonyResponse(), $message, $code));

        $apiHttpClient = new ApiHttpClient($symfonyHttpClient, new NullLogger(), '', '', 1);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($code);

        $apiHttpClient->request('GET', '');
    }

    /**
     * @testWith
     * ["a", "aa", 1, "101", "102", 100, "GET", "aaa", ["a", "a"], ["aa", "aa"], ["aaa", "aaa"]]
     * ["b", "bb", 2, "201", "202", 200, "POST", "bbb", ["b", "b"], ["bb", "bb"], ["bbb", "bbb"]]
     * ["c", "cc", 3, "301", "302", 300, "PUT", "ccc", ["c", "c"], ["cc", "cc"], ["ccc", "ccc"]]
     * ["d", "dd", 4, "401", "402", 400, "PATCH", "ddd", ["d", "d"], ["dd", "dd"], ["ddd", "ddd"]]
     * ["e", "ee", 5, "501", "502", 500, "DELETE", "eee", ["e", "e"], ["ee", "ee"], ["eee", "eee"]]
     */
    public function testRequest(
        string $appId,
        string $appSecret,
        int $timeout,
        string $lastXRateLimitRemaining,
        string $lastXRateLimitLimit,
        int $lastStatusCode,
        string $method,
        string $action,
        array $query,
        array $body,
        array $responseBody
    ): void {
        $constants = ReflectionHelper::getConstants(ApiHttpClient::class);

        $this->assertContains('API_URL', array_keys($constants));
        $this->assertContains('API_VERSION', array_keys($constants));
        $this->assertContains('REQUEST_LOG_FORMAT', array_keys($constants));
        $this->assertContains('RESPONSE_LOG_FORMAT', array_keys($constants));

        $apiUrl = $constants['API_URL'];
        $apiVersion = $constants['API_VERSION'];
        $requestLogFormat = $constants['REQUEST_LOG_FORMAT'];
        $responseLogFormat = $constants['RESPONSE_LOG_FORMAT'];

        $symfonyResponse = new MockSymfonyResponse([
            'x-ratelimit-remaining' => [$lastXRateLimitRemaining],
            'x-ratelimit-limit' => [$lastXRateLimitLimit],
        ], $responseBody, $lastStatusCode);

        $symfonyHttpClient = new MockSymfonyHttpClient($symfonyResponse);

        $psrLogger = new MockPsrLogger();

        $apiHttpClient = new ApiHttpClient($symfonyHttpClient, $psrLogger, $appId, $appSecret, $timeout);

        $uri = sprintf('%s/%s/%s', $apiUrl, $apiVersion, $action);

        $options = [
            'headers' => [
                'X-APP-ID' => $appId,
                'X-APP-SECRET' => $appSecret,
            ],
            'timeout' => $timeout,
        ];

        if (0 === rand(0, 1)) {
            $clientToken = (string) rand(0, 999);

            $apiHttpClient->setClientToken($clientToken);

            $options['headers']['X-CLIENT-TOKEN'] = $clientToken;
        }

        if (0 !== count($query)) {
            $options['query'] = $query;
        }

        if (0 !== strlen(json_encode($body))) {
            $options['body'] = json_encode($body);
            $options['headers']['Content-Type'] = 'application/json';
        }

        $response = $apiHttpClient->request($method, $action, $query, json_encode($body));

        $this->assertEquals($method, $symfonyHttpClient->getRequestMethod());
        $this->assertEquals($uri, $symfonyHttpClient->getRequestUrl());
        $this->assertEquals($options, $symfonyHttpClient->getRequestOptions());
        $this->assertEquals($symfonyResponse->getContent(), $response);

        $logs = $psrLogger->getLogs();

        $this->assertCount(2, $logs);
        $this->assertEquals([
            'debug',
            sprintf($requestLogFormat,
                $method,
                $uri,
                json_encode($options)
            ),
            [],
        ], $logs[0]);
        $this->assertEquals([
            'debug',
            sprintf($responseLogFormat,
                $symfonyResponse->getHeaders()['x-ratelimit-remaining'][0] ?? null,
                $symfonyResponse->getHeaders()['x-ratelimit-limit'][0] ?? null,
                $symfonyResponse->getStatusCode(),
                $symfonyResponse->getContent(),
            ),
            [],
        ], $logs[1]);
    }
}
