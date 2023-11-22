<?php

declare(strict_types=1);

namespace ProdumanApi\Tests\Mock;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class MockSymfonyClientException extends \Exception implements ClientExceptionInterface
{
    private ResponseInterface $response;

    public function __construct(ResponseInterface $response, $message = '', $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->response = $response;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
