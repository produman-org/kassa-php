<?php

declare(strict_types=1);

namespace ProdumanApi\ClientHelper;

use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Exception\JsonResponseException;
use ProdumanApi\Request\Counterparties\ListRequest;
use ProdumanApi\Response\Counterparties\ListDto;

class Counterparties extends AbstractClientHelper
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
            'counterparties',
            ListDto::class,
            $request
        );

        return $result;
    }
}
