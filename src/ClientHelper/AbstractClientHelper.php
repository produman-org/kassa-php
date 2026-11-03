<?php

declare(strict_types=1);

namespace ProdumanApi\ClientHelper;

use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Exception\JsonRequestException;
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
     * @throws JsonRequestException
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
                $content = $request->getRequestContent();
                try {
                    $body = json_encode($content, JSON_THROW_ON_ERROR);
                } catch (\JsonException $error) {
                    throw new JsonRequestException('Unable to encode request as JSON', 0, $error);
                }
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

        try {
            $responseContentArray = json_decode($responseContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $error) {
            throw new JsonResponseException('JSON response format is invalid', 0, $error);
        }

        if (!is_array($responseContentArray)) {
            throw new JsonResponseException('JSON response must decode to an array');
        }

        if (!is_subclass_of($responseType, ResponseInterface::class)) {
            throw new \Exception('Class ' . $responseType . ' must be implements ' . ResponseInterface::class);
        }

        return $responseType::createResponseObject($responseContentArray);
    }
}
