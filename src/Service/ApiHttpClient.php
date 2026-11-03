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
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiHttpClient implements ApiHttpClientInterface
{
    private const API_URL = 'https://api.produman.org/kassa';
    private const API_VERSION = 'v1';

    private ?string $language = null;
    private ?string $clientToken = null;
    private ?string $lastXRateLimitRemaining = null;
    private ?string $lastXRateLimitLimit = null;
    private ?int $lastStatusCode = null;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $appId,
        private string $appSecret,
        private int $timeout,
    ) {
    }

    /**
     * @throws ApiException
     * @throws HttpException
     */
    public function request(string $method, string $action, array $query = [], string $body = ''): string
    {
        $this->lastStatusCode = null;
        $this->lastXRateLimitRemaining = null;
        $this->lastXRateLimitLimit = null;

        $url = getenv('PRODUMAN_API_URL') ?: ($_ENV['PRODUMAN_API_URL'] ?? null) ?: self::API_URL;
        $uri = sprintf('%s/%s/%s', $url, self::API_VERSION, $action);
        $logUri = LoggerHelper::prepareUrl($uri);
        $options = [
            'headers' => ['X-APP-ID' => $this->appId, 'X-APP-SECRET' => $this->appSecret],
            'timeout' => $this->timeout,
            'max_redirects' => 0,
        ];
        if (null !== $this->language) {
            $options['headers']['Accept-Language'] = $this->language;
        }
        if (null !== $this->clientToken) {
            $options['headers']['X-CLIENT-TOKEN'] = $this->clientToken;
        }
        if ([] !== $query) {
            $options['query'] = $query;
        }
        if ('' !== $body) {
            $options['body'] = $body;
            $options['headers']['Content-Type'] = 'application/json';
        }
        if (!$this->logger instanceof NullLogger) {
            $message = sprintf(LoggerHelper::REQUEST_LOG_FORMAT, $method, $logUri);
            $this->logger->info($message);
            $this->logger->debug($message, LoggerHelper::prepareRequestOptionsContext($options));
        }

        try {
            $response = $this->httpClient->request($method, $uri, $options);
            $this->captureResponseMetadata($response);
            // Сохраняем HTTP-исключение Symfony и ответ как исходную причину.
            $content = $response->getContent();

            if (!$this->logger instanceof NullLogger) {
                $message = sprintf(LoggerHelper::RESPONSE_LOG_FORMAT, $method, $logUri, $this->lastStatusCode);
                $this->logger->info($message);
                $this->logger->debug($message, $this->responseContext());
            }

            return $content;
        } catch (ClientExceptionInterface $error) {
            try {
                if (null === $this->lastStatusCode) {
                    $this->captureResponseMetadata($error->getResponse());
                }
                $this->logFailure($error);
                $data = json_decode($error->getResponse()->getContent(false), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException) {
                throw new HttpException($error->getMessage(), $error->getCode(), $error);
            } catch (TransportExceptionInterface $readError) {
                $this->logFailure($readError);
                throw new HttpException($readError->getMessage(), $readError->getCode(), $readError);
            }

            if (!is_array($data)
                || (isset($data['message']) && !is_string($data['message']))
                || (isset($data['code']) && !is_string($data['code']))
                || (isset($data['details']) && !is_array($data['details']))) {
                throw new HttpException($error->getMessage(), $error->getCode(), $error);
            }

            throw new ApiException(
                $data['message'] ?? '',
                $data['code'] ?? '',
                $data['details'] ?? [],
                $this->lastStatusCode,
                $error->getMessage(),
                $error->getCode(),
                $error,
            );
        } catch (TransportExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface $error) {
            $this->logFailure($error);

            throw new HttpException($error->getMessage(), $error->getCode(), $error);
        }
    }

    private function captureResponseMetadata(ResponseInterface $response): void
    {
        $this->lastStatusCode = $response->getStatusCode();
        $headers = $response->getHeaders(false);
        $this->lastXRateLimitRemaining = $headers['x-ratelimit-remaining'][0] ?? null;
        $this->lastXRateLimitLimit = $headers['x-ratelimit-limit'][0] ?? null;
    }

    private function responseContext(): array
    {
        return [
            'lastStatusCode' => $this->lastStatusCode,
            'lastXRateLimitRemaining' => $this->lastXRateLimitRemaining,
            'lastXRateLimitLimit' => $this->lastXRateLimitLimit,
        ];
    }

    private function logFailure(\Throwable $error): void
    {
        if ($this->logger instanceof NullLogger) {
            return;
        }
        $message = sprintf(LoggerHelper::THROW_LOG_FORMAT, $error->getCode(), $error::class);
        $this->logger->error($message);
        $this->logger->debug($message, LoggerHelper::prepareThrowableContext($error) + $this->responseContext());
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
