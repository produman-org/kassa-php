<?php

declare(strict_types=1);

namespace ProdumanApi\ClientHelper;

use ProdumanApi\Exception\ApiException;
use ProdumanApi\Exception\HttpException;
use ProdumanApi\Exception\JsonResponseException;
use ProdumanApi\Request\Integrations\CreateConnectRequest;
use ProdumanApi\Response\Integrations\IntegrationConnectDto;
use ProdumanApi\Response\Integrations\IntegrationDto;

class Integrations extends AbstractClientHelper
{
    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function get(string $id): IntegrationDto
    {
        /** @var IntegrationDto $result */
        $result = $this->send(
            self::METHOD_GET,
            'integrations/' . $id,
            IntegrationDto::class
        );

        return $result;
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function deactivate(string $id): void
    {
        $this->send(
            self::METHOD_DELETE,
            'integrations/' . $id
        );
    }

    /**
     * @throws ApiException
     * @throws HttpException
     * @throws JsonResponseException
     * @throws \Exception
     */
    public function create(CreateConnectRequest $request): IntegrationConnectDto
    {
        /** @var IntegrationConnectDto $result */
        $result = $this->send(
            self::METHOD_POST,
            'integrations/connect',
            IntegrationConnectDto::class,
            $request
        );

        return $result;
    }
}
