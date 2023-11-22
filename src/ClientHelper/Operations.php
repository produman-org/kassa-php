<?php

declare(strict_types=1);

namespace ProdumanApi\ClientHelper;

use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Exception\JsonResponseException;
use ProdumanApi\Interfaces\ResponseInterface;
use ProdumanApi\Request\Operations\AbstractCreateRequest;
use ProdumanApi\Request\Operations\CashEditRequest;
use ProdumanApi\Request\Operations\ListRequest;
use ProdumanApi\Response\Operations\ListDto;
use ProdumanApi\Response\Operations\OperationDto;

class Operations extends AbstractClientHelper
{
    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function list(?ListRequest $request = null): ResponseInterface
    {
        return $this->send(
            self::METHOD_GET,
            'operations',
            ListDto::class,
            $request
        );
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function get(string $id): ResponseInterface
    {
        return $this->send(
            self::METHOD_GET,
            'operations/' . $id,
            OperationDto::class
        );
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function create(?AbstractCreateRequest $request = null): ResponseInterface
    {
        return $this->send(
            self::METHOD_POST,
            'operations',
            OperationDto::class,
            $request
        );
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function retry(string $id): ResponseInterface
    {
        return $this->send(
            self::METHOD_POST,
            'operations/' . $id . '/retry',
            OperationDto::class
        );
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function cashMovementEdit(string $id, ?CashEditRequest $request = null): ResponseInterface
    {
        return $this->send(
            self::METHOD_PATCH,
            'operations/cash-movement/' . $id,
            OperationDto::class,
            $request
        );
    }
}
