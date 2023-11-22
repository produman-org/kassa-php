<?php

declare(strict_types=1);

namespace ProdumanApi\Interfaces;

interface ApiHttpClientInterface
{
    /**
     * Http request to ProdumanAPI.
     */
    public function request(string $method, string $action, array $query = [], string $body = ''): string;

    public function getLastXRateLimitRemaining(): ?string;

    public function getLastXRateLimitLimit(): ?string;

    public function getLastStatusCode(): ?int;
}
