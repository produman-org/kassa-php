<?php

declare(strict_types=1);

namespace ProdumanApi\Exception;

class ApiException extends \RuntimeException
{
    private string $apiMessage;

    /**
     * Potential values are 'NOT_FOUND', 'UNAUTHORIZED', 'ACCESS_DENIED', 'VALIDATION_ERROR', 'INTERNAL_SERVER_ERROR', 'NOT_EXIST', 'TOO_MANY_REQUESTS', 'BAD_REQUEST', 'PAYMENT_REQUIRED'.
     */
    private string $apiCode;

    private array $apiDetails;

    private int $statusCode;

    public function __construct(string $apiMessage, string $apiCode, array $apiDetails, int $statusCode, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->apiMessage = $apiMessage;
        $this->apiCode = $apiCode;
        $this->apiDetails = $apiDetails;
        $this->statusCode = $statusCode;
    }

    public function getApiMessage(): string
    {
        return $this->apiMessage;
    }

    public function getApiCode(): string
    {
        return $this->apiCode;
    }

    public function getApiDetails(): array
    {
        return $this->apiDetails;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
