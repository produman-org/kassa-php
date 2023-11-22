<?php

declare(strict_types=1);

namespace ProdumanApi\Client;

use ProdumanApi\ClientHelper\Integrations;
use ProdumanApi\ClientHelper\Webhooks;
use ProdumanApi\Interfaces\ApiHttpClientInterface;

/**
 * @see \ProdumanApi\Tests\Client\ClientTest
 */
class ApplicationClient extends AbstractClient
{
    public Integrations $integrations;

    public Webhooks $webhooks;

    public function __construct(ApiHttpClientInterface $apiClient)
    {
        parent::__construct($apiClient);

        $this->integrations = new Integrations($apiClient);

        $this->webhooks = new Webhooks($apiClient);
    }
}
