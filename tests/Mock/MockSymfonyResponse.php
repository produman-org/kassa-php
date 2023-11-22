<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Mock;

use Symfony\Contracts\HttpClient\ResponseInterface;

class MockSymfonyResponse implements ResponseInterface
{
    private array $header;

    private array $body;

    private int $statusCode;

    public function __construct(array $header = [], array $body = [], int $statusCode = 200)
    {
        $this->header = $header;
        $this->body = $body;
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(bool $throw = true): array
    {
        return $this->header;
    }

    public function getContent(bool $throw = true): string
    {
        return json_encode($this->body);
    }

    public function toArray(bool $throw = true): array
    {
        return $this->body;
    }

    public function cancel(): void
    {
    }

    public function getInfo(?string $type = null)
    {
    }
}
