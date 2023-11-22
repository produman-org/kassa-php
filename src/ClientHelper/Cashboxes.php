<?php

declare(strict_types=1);

namespace ProdumanApi\ClientHelper;

use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Exception\JsonResponseException;
use ProdumanApi\Request\Cashboxes\ListRequest;
use ProdumanApi\Response\Cashboxes\CashboxDto;
use ProdumanApi\Response\Cashboxes\ListDto;

class Cashboxes extends AbstractClientHelper
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
            'cashboxes',
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
    public function get(string $id): CashboxDto
    {
        /** @var CashboxDto $result */
        $result = $this->send(
            self::METHOD_GET,
            'cashboxes/' . $id,
            CashboxDto::class
        );

        return $result;
    }
}
