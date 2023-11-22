<?php

declare(strict_types=1);

namespace ProdumanApi\Client;

use ProdumanApi\ClientHelper\Cashboxes;
use ProdumanApi\ClientHelper\CashMovementCategories;
use ProdumanApi\ClientHelper\Counterparties;
use ProdumanApi\ClientHelper\Employees;
use ProdumanApi\ClientHelper\Operations;
use ProdumanApi\ClientHelper\Orders;
use ProdumanApi\Interfaces\ApiHttpClientInterface;

/**
 * @see \ProdumanApi\Tests\Client\ClientTest
 */
class Client extends AbstractClient
{
    public Cashboxes $cashboxes;

    public CashMovementCategories $cashMovementCategories;

    public Counterparties $counterparties;

    public Employees $employees;

    public Operations $operations;

    public Orders $orders;

    public function __construct(ApiHttpClientInterface $apiClient)
    {
        parent::__construct($apiClient);

        $this->cashboxes = new Cashboxes($apiClient);

        $this->cashMovementCategories = new CashMovementCategories($apiClient);

        $this->counterparties = new Counterparties($apiClient);

        $this->employees = new Employees($apiClient);

        $this->operations = new Operations($apiClient);

        $this->orders = new Orders($apiClient);
    }
}
