<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Mock;

use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class MockSymfonyHttpClient implements HttpClientInterface
{
    private ?MockSymfonyResponse $response;

    private ?\Exception $exception;

    private ?string $requestMethod = null;

    private ?string $requestUrl = null;

    private ?array $requestOptions = null;

    public function __construct(?MockSymfonyResponse $response = null, ?\Exception $exception = null)
    {
        $this->response = $response;
        $this->exception = $exception;
    }

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        $this->requestMethod = $method;
        $this->requestUrl = $url;
        $this->requestOptions = $options;

        if (null !== $this->exception) {
            throw $this->exception;
        }

        return $this->response ?? new MockSymfonyResponse();
    }

    public function getRequestMethod(): ?string
    {
        return $this->requestMethod;
    }

    public function getRequestUrl(): ?string
    {
        return $this->requestUrl;
    }

    public function getRequestOptions(): ?array
    {
        return $this->requestOptions;
    }

    public function stream($responses, ?float $timeout = null): ResponseStreamInterface
    {
        return new ResponseStream(new \Generator());
    }
}
