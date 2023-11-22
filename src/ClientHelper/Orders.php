<?php

declare(strict_types=1);

namespace ProdumanApi\ClientHelper;

use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Exception\JsonResponseException;
use ProdumanApi\Request\Orders\CreateRequest;
use ProdumanApi\Request\Orders\ListRequest;
use ProdumanApi\Response\Orders\ListDto;
use ProdumanApi\Response\Orders\OrderDto;

class Orders extends AbstractClientHelper
{
    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function list(?ListRequest $request = null): ListDto
    {
        /** @var ListDto $result */
        $result = $this->send(
            self::METHOD_GET,
            'orders',
            ListDto::class,
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
    public function get(string $id): OrderDto
    {
        /** @var OrderDto $result */
        $result = $this->send(
            self::METHOD_GET,
            'orders/' . $id,
            OrderDto::class
        );

        return $result;
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function create(CreateRequest $request): OrderDto
    {
        /** @var OrderDto $result */
        $result = $this->send(
            self::METHOD_POST,
            'orders',
            OrderDto::class,
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
    public function update(string $id, CreateRequest $request): OrderDto
    {
        /** @var OrderDto $result */
        $result = $this->send(
            self::METHOD_PUT,
            'orders/' . $id,
            OrderDto::class,
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
    public function delete(string $id): void
    {
        $this->send(
            self::METHOD_DELETE,
            'orders/' . $id
        );
    }
}
