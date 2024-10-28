<?php

declare(strict_types=1);

namespace ProdumanApi\Service;

use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Interfaces\ApiHttpClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @see \ProdumanApi\Tests\Service\ApiHttpClientTest
 */
class ApiHttpClient implements ApiHttpClientInterface
{
    private const API_URL = 'https://api.produman.org/kassa';

    private const API_VERSION = 'v1';

    private HttpClientInterface $httpClient;

    private LoggerInterface $logger;

    private string $appId;

    private string $appSecret;

    private int $timeout;

    private ?string $language = null;

    private ?string $clientToken = null;

    private ?string $lastXRateLimitRemaining = null;

    private ?string $lastXRateLimitLimit = null;

    private ?int $lastStatusCode = null;

    public function __construct(HttpClientInterface $httpClient, LoggerInterface $logger, string $appId, string $appSecret, int $timeout)
    {
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->timeout = $timeout;
    }

    /**
     * @throws ApiException
     * @throws HttpException
     */
    public function request(string $method, string $action, array $query = [], string $body = ''): string
    {
        $url = self::API_URL;

        if (!empty(getenv('PRODUMAN_API_URL'))) {
            $url = getenv('PRODUMAN_API_URL');
        } elseif (!empty($_ENV['PRODUMAN_API_URL'])) {
            $url = $_ENV['PRODUMAN_API_URL'];
        }

        $uri = sprintf('%s/%s/%s', $url, self::API_VERSION, $action);

        $options = [
            'headers' => [
                'X-APP-ID' => $this->appId,
                'X-APP-SECRET' => $this->appSecret,
            ],
            'timeout' => $this->timeout,
        ];

        if (null !== $this->language) {
            $options['headers']['Accept-Language'] = $this->language;
        }

        if (null !== $this->clientToken) {
            $options['headers']['X-CLIENT-TOKEN'] = $this->clientToken;
        }

        if (0 !== count($query)) {
            $options['query'] = $query;
        }

        if (0 !== strlen($body)) {
            $options['body'] = $body;
            $options['headers']['Content-Type'] = 'application/json';
        }

        if (!($this->logger instanceof NullLogger)) {
            $this->logger->info(sprintf(
                LoggerHelper::REQUEST_LOG_FORMAT,
                $method,
                $uri
            ));

            $this->logger->debug(sprintf(
                LoggerHelper::REQUEST_LOG_FORMAT,
                $method,
                $uri
            ), LoggerHelper::prepareRequestOptionsContext($options));
        }

        try {
            $response = $this->httpClient->request($method, $uri, $options);

            $responseHeaders = $response->getHeaders();

            $this->lastXRateLimitRemaining = $responseHeaders['x-ratelimit-remaining'][0] ?? null;
            $this->lastXRateLimitLimit = $responseHeaders['x-ratelimit-limit'][0] ?? null;
            $this->lastStatusCode = $response->getStatusCode();

            $responseContent = $response->getContent();

            if (!($this->logger instanceof NullLogger)) {
                $this->logger->info(sprintf(
                    LoggerHelper::RESPONSE_LOG_FORMAT,
                    $method,
                    $uri,
                    $this->lastStatusCode
                ));

                $this->logger->debug(sprintf(
                    LoggerHelper::RESPONSE_LOG_FORMAT,
                    $method,
                    $uri,
                    $this->lastStatusCode
                ), [
                    'lastXRateLimitRemaining' => $this->lastXRateLimitRemaining,
                    'lastXRateLimitLimit' => $this->lastXRateLimitLimit,
                    'content' => $responseContent,
                ]);
            }

            return $responseContent;
        } catch (ClientExceptionInterface $e) {
            if (!($this->logger instanceof NullLogger)) {
                $this->logger->error(sprintf(
                    LoggerHelper::THROW_LOG_FORMAT,
                    $e->getCode(),
                    $e->getMessage()
                ));

                $this->logger->debug(sprintf(
                    LoggerHelper::THROW_LOG_FORMAT,
                    $e->getCode(),
                    $e->getMessage()
                ), LoggerHelper::prepareThrowableContext($e));
            }

            try {
                $responseHeaders = $e->getResponse()->getHeaders(false);

                $this->lastXRateLimitRemaining = $responseHeaders['x-ratelimit-remaining'][0] ?? null;
                $this->lastXRateLimitLimit = $responseHeaders['x-ratelimit-limit'][0] ?? null;
                $this->lastStatusCode = $e->getResponse()->getStatusCode();

                $responseContentJson = $e->getResponse()->getContent(false);

                $responseContent = json_decode($responseContentJson, true);

                if (!is_array($responseContent)) {
                    throw $e;
                }

                if (isset($responseContent['message']) && !is_string($responseContent['message'])) {
                    throw $e;
                }

                if (isset($responseContent['code']) && !is_string($responseContent['code'])) {
                    throw $e;
                }

                if (isset($responseContent['details']) && !is_array($responseContent['details'])) {
                    throw $e;
                }

                throw new ApiException(
                    $responseContent['message'] ?? '',
                    $responseContent['code'] ?? '',
                    $responseContent['details'] ?? [],
                    $e->getResponse()->getStatusCode(),
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            } catch (ClientExceptionInterface|TransportExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
                throw new HttpException($e->getMessage(), $e->getCode(), $e);
            }
        } catch (TransportExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $e) {
            if (!($this->logger instanceof NullLogger)) {
                $this->logger->error(sprintf(
                    LoggerHelper::THROW_LOG_FORMAT,
                    $e->getCode(),
                    $e->getMessage()
                ));

                $this->logger->debug(sprintf(
                    LoggerHelper::THROW_LOG_FORMAT,
                    $e->getCode(),
                    $e->getMessage()
                ), LoggerHelper::prepareThrowableContext($e));
            }

            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function setClientToken(string $clientToken): void
    {
        $this->clientToken = $clientToken;
    }

    public function setLanguage(?string $language): void
    {
        $this->language = $language;
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
}
