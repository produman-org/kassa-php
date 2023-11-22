<?php

declare(strict_types=1);

namespace ProdumanApi\ClientHelper;

use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Exception\JsonResponseException;
use ProdumanApi\Request\Webhooks\EndpointCreateRequest;
use ProdumanApi\Request\Webhooks\EndpointListRequest;
use ProdumanApi\Request\Webhooks\EndpointTestRequest;
use ProdumanApi\Response\Webhooks\EndpointDto;
use ProdumanApi\Response\Webhooks\EndpointListDto;

class Webhooks extends AbstractClientHelper
{
    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function endpointList(?EndpointListRequest $request = null): EndpointListDto
    {
        /** @var EndpointListDto $result */
        $result = $this->send(
            self::METHOD_GET,
            'application-webhooks/endpoints',
            EndpointListDto::class,
            $request
        );

        return $result;
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function endpointGet(string $id): EndpointDto
    {
        /** @var EndpointDto $result */
        $result = $this->send(
            self::METHOD_GET,
            'application-webhooks/endpoints/' . $id,
            EndpointDto::class
        );

        return $result;
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function endpointCreate(EndpointCreateRequest $request): EndpointDto
    {
        /** @var EndpointDto $result */
        $result = $this->send(
            self::METHOD_POST,
            'application-webhooks/endpoints',
            EndpointDto::class,
            $request
        );

        return $result;
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function endpointUpdate(string $id, EndpointCreateRequest $request): EndpointDto
    {
        /** @var EndpointDto $result */
        $result = $this->send(
            self::METHOD_PUT,
            'application-webhooks/endpoints/' . $id,
            EndpointDto::class,
            $request
        );

        return $result;
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function endpointDelete(string $id): void
    {
        $this->send(
            self::METHOD_DELETE,
            'application-webhooks/endpoints/' . $id
        );
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function endpointTest(EndpointTestRequest $request): void
    {
        $this->send(
            self::METHOD_POST,
            'application-webhooks/test',
            null,
            $request
        );
    }
}
