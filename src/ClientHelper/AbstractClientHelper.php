<?php

declare(strict_types=1);

namespace ProdumanApi\ClientHelper;

use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Exception\JsonResponseException;
use ProdumanApi\Interfaces\ApiHttpClientInterface;
use ProdumanApi\Interfaces\RequestInterface;
use ProdumanApi\Interfaces\ResponseInterface;

abstract class AbstractClientHelper
{
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_PATCH = 'PATCH';
    public const METHOD_DELETE = 'DELETE';

    private ApiHttpClientInterface $apiClient;

    public function __construct(ApiHttpClientInterface $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    protected function send(string $method, string $action, ?string $responseType = null, ?RequestInterface $request = null): ?ResponseInterface
    {
        $query = [];
        $body = '';

        if (null !== $request) {
            if (self::METHOD_GET === $method) {
                $query = $request->getRequestContent();
            } else {
                $body = json_encode($request->getRequestContent());
            }
        }

        $responseContent = $this->apiClient->request(
            $method,
            $action,
            $query,
            $body,
        );

        if (null === $responseType) {
            return null;
        }

        $responseContentArray = json_decode($responseContent, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonResponseException('JSON response format is invalid');
        }

        if (!is_subclass_of($responseType, ResponseInterface::class)) {
            throw new \Exception('Class ' . $responseType . ' must be implements ' . ResponseInterface::class);
        }

        return $responseType::createResponseObject($responseContentArray);
    }
}
